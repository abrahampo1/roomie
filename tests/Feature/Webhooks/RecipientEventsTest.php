<?php

use App\Jobs\DeliverWebhookJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\User;
use App\Models\Webhook;
use App\Services\Email\EmailTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

/**
 * @return array{user: User, campaign: Campaign, recipient: CampaignRecipient, webhook: Webhook}
 */
function makeTrackedRecipient(array $events = ['*']): array
{
    $user = User::factory()->create();

    $campaign = Campaign::create([
        'user_id' => $user->id,
        'objective' => 'test objective here',
        'status' => 'completed',
        'aggressiveness' => 2,
        'persuasion_patterns' => 2,
    ]);

    $recipient = CampaignRecipient::create([
        'campaign_id' => $campaign->id,
        'email' => 'guest@example.invalid',
        'status' => 'sent',
    ]);

    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'Hook',
        'url' => 'https://example.com/hook',
        'secret' => 'whsec_x',
        'events' => $events,
        'active' => true,
    ]);

    return compact('user', 'campaign', 'recipient', 'webhook');
}

it('fires recipient.opened on the FIRST open only', function () {
    ['recipient' => $recipient] = makeTrackedRecipient();
    $service = new EmailTrackingService;

    $service->recordOpen($recipient);
    Queue::assertPushed(DeliverWebhookJob::class, 1);

    // Second open should NOT queue another delivery
    $service->recordOpen($recipient->fresh());
    Queue::assertPushed(DeliverWebhookJob::class, 1);
});

it('fires recipient.clicked AND recipient.converted on the first click', function () {
    ['recipient' => $recipient] = makeTrackedRecipient();
    $service = new EmailTrackingService;

    $service->recordClick($recipient);

    // One clicked, one converted = 2 deliveries queued
    Queue::assertPushed(DeliverWebhookJob::class, 2);
});

it('does not fire recipient.clicked again on the second click', function () {
    ['recipient' => $recipient] = makeTrackedRecipient();
    $service = new EmailTrackingService;

    $service->recordClick($recipient);
    Queue::assertPushed(DeliverWebhookJob::class, 2);

    // Reset and fire again — second click should be a no-op webhook-wise
    Queue::fake();
    $service->recordClick($recipient->fresh());
    Queue::assertNothingPushed();
});

it('fires recipient.unsubscribed', function () {
    ['recipient' => $recipient] = makeTrackedRecipient();
    $service = new EmailTrackingService;

    $service->recordUnsubscribe($recipient);

    Queue::assertPushed(DeliverWebhookJob::class, 1);
});
