<?php

use App\Jobs\DeliverWebhookJob;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{webhook: Webhook, delivery: WebhookDelivery}
 */
function makeDelivery(array $overrides = []): array
{
    $user = User::factory()->create();

    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'Test',
        'url' => 'https://example.com/hook',
        'secret' => 'whsec_testsecret',
        'events' => ['*'],
        'active' => true,
        'consecutive_failures' => $overrides['consecutive_failures'] ?? 0,
    ]);

    $eventId = (string) Str::uuid();
    $delivery = WebhookDelivery::create([
        'webhook_id' => $webhook->id,
        'event_type' => 'campaign.completed',
        'event_id' => $eventId,
        'payload' => [
            'id' => $eventId,
            'type' => 'campaign.completed',
            'created' => 1744080000,
            'data' => ['campaign_id' => 1],
        ],
        'attempt' => 1,
    ]);

    return ['webhook' => $webhook, 'delivery' => $delivery];
}

it('sends a signed POST and marks delivery on 2xx', function () {
    Http::fake([
        'https://example.com/hook' => Http::response('ok', 200),
    ]);

    ['webhook' => $webhook, 'delivery' => $delivery] = makeDelivery();

    (new DeliverWebhookJob($delivery->id))->handle();

    $delivery->refresh();
    expect($delivery->status_code)->toBe(200)
        ->and($delivery->delivered_at)->not->toBeNull();

    $webhook->refresh();
    expect($webhook->consecutive_failures)->toBe(0)
        ->and($webhook->last_status_code)->toBe(200);

    Http::assertSent(function ($request) {
        $signature = $request->header('X-Roomie-Signature')[0] ?? '';

        return str_contains($signature, 't=')
            && str_contains($signature, 'v1=')
            && $request->header('X-Roomie-Event')[0] === 'campaign.completed'
            && $request->header('X-Roomie-Delivery')[0] !== ''
            && $request->header('User-Agent')[0] === 'Roomie-Webhooks/1.0';
    });
});

it('resets consecutive_failures on a successful delivery', function () {
    Http::fake([
        'https://example.com/hook' => Http::response('ok', 200),
    ]);

    ['webhook' => $webhook, 'delivery' => $delivery] = makeDelivery(['consecutive_failures' => 4]);

    (new DeliverWebhookJob($delivery->id))->handle();

    $webhook->refresh();
    expect($webhook->consecutive_failures)->toBe(0);
});

it('throws and records failure on 5xx so the queue retries', function () {
    Http::fake([
        'https://example.com/hook' => Http::response('nope', 500),
    ]);

    ['delivery' => $delivery] = makeDelivery();

    expect(fn () => (new DeliverWebhookJob($delivery->id))->handle())
        ->toThrow(RuntimeException::class);

    $delivery->refresh();
    expect($delivery->status_code)->toBe(500)
        ->and($delivery->delivered_at)->toBeNull()
        ->and($delivery->error)->toContain('HTTP 500');
});

it('auto-disables the webhook after the failure threshold on failed()', function () {
    ['webhook' => $webhook, 'delivery' => $delivery] = makeDelivery(['consecutive_failures' => 9]);

    (new DeliverWebhookJob($delivery->id))->failed(new RuntimeException('boom'));

    $webhook->refresh();
    expect($webhook->consecutive_failures)->toBe(10)
        ->and($webhook->active)->toBeFalse();
});

it('leaves the webhook active below the threshold', function () {
    ['webhook' => $webhook, 'delivery' => $delivery] = makeDelivery(['consecutive_failures' => 2]);

    (new DeliverWebhookJob($delivery->id))->failed(new RuntimeException('boom'));

    $webhook->refresh();
    expect($webhook->consecutive_failures)->toBe(3)
        ->and($webhook->active)->toBeTrue();
});
