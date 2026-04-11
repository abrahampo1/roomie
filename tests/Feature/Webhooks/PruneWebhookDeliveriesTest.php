<?php

use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('deletes deliveries older than the retention window', function () {
    $user = User::factory()->create();
    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'X',
        'url' => 'https://example.com/x',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    $old = WebhookDelivery::create([
        'webhook_id' => $webhook->id,
        'event_type' => 'campaign.completed',
        'event_id' => (string) Str::uuid(),
        'payload' => ['type' => 'campaign.completed'],
        'attempt' => 1,
    ]);
    $old->forceFill(['created_at' => now()->subDays(30)])->save();

    $recent = WebhookDelivery::create([
        'webhook_id' => $webhook->id,
        'event_type' => 'campaign.completed',
        'event_id' => (string) Str::uuid(),
        'payload' => ['type' => 'campaign.completed'],
        'attempt' => 1,
    ]);

    $this->artisan('webhooks:prune-deliveries')->assertSuccessful();

    expect(WebhookDelivery::find($old->id))->toBeNull()
        ->and(WebhookDelivery::find($recent->id))->not->toBeNull();
});
