<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\Webhooks\WebhookSigner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Delivers a single WebhookDelivery row. Signs the payload, POSTs it, and
 * records the outcome on the row. Non-2xx and network errors throw so
 * Laravel's retry machinery re-queues the job with the configured backoff.
 *
 * After 10 consecutive failed events on a webhook (across deliveries, not
 * attempts) the webhook is auto-disabled so misconfigured endpoints stop
 * burning queue capacity.
 */
class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 120, 300, 900];

    public int $timeout = 15;

    private const AUTO_DISABLE_THRESHOLD = 10;

    private const RESPONSE_BODY_MAX = 2048;

    private const HTTP_TIMEOUT_SECONDS = 10;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::query()->with('webhook')->find($this->deliveryId);

        if ($delivery === null) {
            return;
        }

        $webhook = $delivery->webhook;

        if ($webhook === null || ! $webhook->active) {
            return;
        }

        $delivery->update(['attempt' => $this->attempts()]);

        $body = (string) json_encode($delivery->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = now()->timestamp;
        $signature = WebhookSigner::sign($webhook->secret, $body, $timestamp);

        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Roomie-Webhooks/1.0',
                'X-Roomie-Event' => $delivery->event_type,
                'X-Roomie-Delivery' => $delivery->event_id,
                'X-Roomie-Signature' => $signature,
            ])
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->withBody($body, 'application/json')
                ->post($webhook->url);
        } catch (ConnectionException $e) {
            $this->recordFailure($delivery, null, null, $e->getMessage(), $startedAt);

            throw $e;
        } catch (Throwable $e) {
            $this->recordFailure($delivery, null, null, $e->getMessage(), $startedAt);

            throw $e;
        }

        $statusCode = $response->status();
        $responseBody = mb_substr((string) $response->body(), 0, self::RESPONSE_BODY_MAX);

        if ($response->successful()) {
            $delivery->update([
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'delivered_at' => now(),
                'error' => null,
            ]);

            $webhook->update([
                'consecutive_failures' => 0,
                'last_triggered_at' => now(),
                'last_status_code' => $statusCode,
            ]);

            return;
        }

        $this->recordFailure($delivery, $statusCode, $responseBody, "HTTP {$statusCode}", $startedAt);

        throw new \RuntimeException("Webhook delivery failed with HTTP {$statusCode}");
    }

    /**
     * Called by Laravel when all retries are exhausted. Bumps the webhook's
     * consecutive_failures (this counts the entire event as failed) and
     * auto-disables once the threshold is hit.
     */
    public function failed(Throwable $exception): void
    {
        $delivery = WebhookDelivery::query()->with('webhook')->find($this->deliveryId);

        if ($delivery === null || $delivery->webhook === null) {
            return;
        }

        $webhook = $delivery->webhook;
        $webhook->increment('consecutive_failures');
        $webhook->update([
            'last_triggered_at' => now(),
            'last_status_code' => $delivery->status_code,
        ]);

        if ($webhook->consecutive_failures >= self::AUTO_DISABLE_THRESHOLD) {
            $webhook->update(['active' => false]);

            Log::warning('Webhook auto-disabled after consecutive failures', [
                'webhook_id' => $webhook->id,
                'user_id' => $webhook->user_id,
                'consecutive_failures' => $webhook->consecutive_failures,
            ]);
        }
    }

    private function recordFailure(
        WebhookDelivery $delivery,
        ?int $statusCode,
        ?string $responseBody,
        string $error,
        float $startedAt,
    ): void {
        $delivery->update([
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            'error' => mb_substr($error, 0, 1000),
        ]);
    }
}
