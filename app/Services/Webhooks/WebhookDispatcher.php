<?php

namespace App\Services\Webhooks;

use App\Jobs\DeliverWebhookJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Single entry point for firing webhook events. All call sites in the
 * pipeline, send subsystem and tracking service call into one of the
 * static helpers on this class.
 *
 * Dispatching is fire-and-forget — any failure while enqueueing a delivery
 * is logged and swallowed so a broken webhook never poisons the code path
 * that triggered the event.
 */
class WebhookDispatcher
{
    /**
     * Fan out an event to every matching active webhook owned by the
     * given user. For each match, a WebhookDelivery row is created and a
     * DeliverWebhookJob is queued.
     *
     * @param  array<string, mixed>  $data
     */
    public static function dispatch(?User $user, string $eventType, array $data): void
    {
        if ($user === null) {
            return;
        }

        try {
            $webhooks = Webhook::query()
                ->where('user_id', $user->id)
                ->where('active', true)
                ->get()
                ->filter(fn (Webhook $w) => $w->subscribesTo($eventType));

            if ($webhooks->isEmpty()) {
                return;
            }

            foreach ($webhooks as $webhook) {
                $eventId = (string) Str::uuid();
                $payload = self::envelope($eventId, $eventType, $data);

                $delivery = WebhookDelivery::create([
                    'webhook_id' => $webhook->id,
                    'event_type' => $eventType,
                    'event_id' => $eventId,
                    'payload' => $payload,
                    'attempt' => 1,
                ]);

                DeliverWebhookJob::dispatch($delivery->id);
            }
        } catch (Throwable $e) {
            Log::warning('WebhookDispatcher: failed to enqueue deliveries', [
                'event_type' => $eventType,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Convenience helper for campaign.* events — resolves the owning user
     * from the campaign relationship automatically.
     *
     * @param  array<string, mixed>  $data
     */
    public static function dispatchCampaignEvent(Campaign $campaign, string $eventType, array $data): void
    {
        self::dispatch($campaign->user, $eventType, $data);
    }

    /**
     * Convenience helper for recipient.* events — loads the owning user
     * via the campaign relationship with a single query.
     *
     * @param  array<string, mixed>  $data
     */
    public static function dispatchRecipientEvent(CampaignRecipient $recipient, string $eventType, array $data): void
    {
        $campaign = $recipient->campaign()->with('user')->first();

        if ($campaign === null) {
            return;
        }

        self::dispatch($campaign->user, $eventType, array_merge([
            'campaign_id' => $campaign->id,
        ], $data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function envelope(string $eventId, string $eventType, array $data): array
    {
        return [
            'id' => $eventId,
            'type' => $eventType,
            'created' => now()->timestamp,
            'data' => $data,
        ];
    }
}
