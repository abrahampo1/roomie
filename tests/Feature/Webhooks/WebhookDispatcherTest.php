<?php

use App\Jobs\DeliverWebhookJob;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

it('fans out one delivery per matching active webhook', function () {
    $user = User::factory()->create();

    Webhook::create([
        'user_id' => $user->id,
        'name' => 'Slack',
        'url' => 'https://example.com/slack',
        'secret' => 'whsec_a',
        'events' => ['campaign.completed'],
        'active' => true,
    ]);
    Webhook::create([
        'user_id' => $user->id,
        'name' => 'Wildcard',
        'url' => 'https://example.com/all',
        'secret' => 'whsec_b',
        'events' => ['*'],
        'active' => true,
    ]);

    WebhookDispatcher::dispatch($user, 'campaign.completed', ['campaign_id' => 1]);

    expect(WebhookDelivery::count())->toBe(2);
    Queue::assertPushed(DeliverWebhookJob::class, 2);
});

it('skips webhooks that do not subscribe to the event', function () {
    $user = User::factory()->create();

    Webhook::create([
        'user_id' => $user->id,
        'name' => 'Opens only',
        'url' => 'https://example.com/opens',
        'secret' => 'whsec_x',
        'events' => ['recipient.opened'],
        'active' => true,
    ]);

    WebhookDispatcher::dispatch($user, 'campaign.completed', []);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNothingPushed();
});

it('skips inactive webhooks', function () {
    $user = User::factory()->create();

    Webhook::create([
        'user_id' => $user->id,
        'name' => 'Disabled',
        'url' => 'https://example.com/off',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => false,
    ]);

    WebhookDispatcher::dispatch($user, 'campaign.completed', []);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNothingPushed();
});

it('does not dispatch to webhooks owned by other users', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    Webhook::create([
        'user_id' => $stranger->id,
        'name' => 'Stranger',
        'url' => 'https://example.com/stranger',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    WebhookDispatcher::dispatch($owner, 'campaign.completed', []);

    expect(WebhookDelivery::count())->toBe(0);
});

it('wraps the payload in a canonical envelope', function () {
    $user = User::factory()->create();

    Webhook::create([
        'user_id' => $user->id,
        'name' => 'WH',
        'url' => 'https://example.com/wh',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    WebhookDispatcher::dispatch($user, 'campaign.completed', ['campaign_id' => 42]);

    $delivery = WebhookDelivery::first();
    expect($delivery->payload)
        ->toHaveKeys(['id', 'type', 'created', 'data'])
        ->and($delivery->payload['type'])->toBe('campaign.completed')
        ->and($delivery->payload['data'])->toBe(['campaign_id' => 42]);
});

it('is a no-op when user is null', function () {
    WebhookDispatcher::dispatch(null, 'campaign.completed', []);

    expect(WebhookDelivery::count())->toBe(0);
    Queue::assertNothingPushed();
});
