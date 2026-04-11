<?php

use App\Jobs\DeliverWebhookJob;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

/**
 * @return array{user: User, token: string}
 */
function userWithApiToken(): array
{
    $user = User::factory()->create();
    $token = $user->generateApiToken();

    return compact('user', 'token');
}

function authHeaders(string $token): array
{
    return [
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ];
}

it('rejects requests without an API token', function () {
    $this->getJson('/api/v1/webhooks')->assertStatus(401);
});

it('creates a webhook and returns the plaintext secret exactly once', function () {
    ['token' => $token] = userWithApiToken();

    $response = $this->withHeaders(authHeaders($token))
        ->postJson('/api/v1/webhooks', [
            'name' => 'Slack',
            'url' => 'https://example.com/slack',
            'events' => ['campaign.completed', 'recipient.converted'],
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('name', 'Slack')
        ->assertJsonPath('url', 'https://example.com/slack')
        ->assertJsonStructure(['id', 'secret', 'events', 'created_at']);

    $webhookId = $response->json('id');

    // Secret MUST NOT appear in subsequent GETs
    $show = $this->withHeaders(authHeaders($token))
        ->getJson("/api/v1/webhooks/{$webhookId}");
    $show->assertStatus(200)
        ->assertJsonMissing(['secret' => $response->json('secret')]);
    expect($show->json('data'))->not->toHaveKey('secret');
});

it('rejects http:// URLs', function () {
    ['token' => $token] = userWithApiToken();

    $this->withHeaders(authHeaders($token))
        ->postJson('/api/v1/webhooks', [
            'name' => 'Bad',
            'url' => 'http://example.com/hook',
            'events' => ['*'],
        ])
        ->assertStatus(422);
});

it('rejects unknown event types', function () {
    ['token' => $token] = userWithApiToken();

    $this->withHeaders(authHeaders($token))
        ->postJson('/api/v1/webhooks', [
            'name' => 'Bad',
            'url' => 'https://example.com/hook',
            'events' => ['campaign.exploded'],
        ])
        ->assertStatus(422)
        ->assertJsonPath('error', 'invalid_events');
});

it('returns 403 when accessing another user webhook', function () {
    ['token' => $tokenA, 'user' => $userA] = userWithApiToken();
    ['token' => $tokenB, 'user' => $userB] = userWithApiToken();

    $webhook = Webhook::create([
        'user_id' => $userB->id,
        'name' => 'Theirs',
        'url' => 'https://example.com/theirs',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    $this->withHeaders(authHeaders($tokenA))
        ->getJson("/api/v1/webhooks/{$webhook->id}")
        ->assertStatus(403)
        ->assertJsonPath('error', 'forbidden');
});

it('rotates the secret and returns the new value', function () {
    ['token' => $token, 'user' => $user] = userWithApiToken();

    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'Rotatable',
        'url' => 'https://example.com/r',
        'secret' => 'whsec_old',
        'events' => ['*'],
        'active' => true,
    ]);

    $response = $this->withHeaders(authHeaders($token))
        ->postJson("/api/v1/webhooks/{$webhook->id}/rotate-secret");

    $response->assertStatus(200)->assertJsonStructure(['id', 'secret', 'rotated_at']);
    expect($response->json('secret'))->toStartWith('whsec_');
    expect($webhook->fresh()->secret)->toBe($response->json('secret'));
});

it('queues a test event on send-test', function () {
    Queue::fake();

    ['token' => $token, 'user' => $user] = userWithApiToken();

    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'Testable',
        'url' => 'https://example.com/t',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    $response = $this->withHeaders(authHeaders($token))
        ->postJson("/api/v1/webhooks/{$webhook->id}/test");

    $response->assertStatus(200)->assertJsonPath('queued', true);
    Queue::assertPushed(DeliverWebhookJob::class);
    expect(WebhookDelivery::where('webhook_id', $webhook->id)->where('event_type', 'webhook.test')->exists())->toBeTrue();
});

it('deletes a webhook', function () {
    ['token' => $token, 'user' => $user] = userWithApiToken();

    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'Gone',
        'url' => 'https://example.com/g',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    $this->withHeaders(authHeaders($token))
        ->deleteJson("/api/v1/webhooks/{$webhook->id}")
        ->assertStatus(200)
        ->assertJsonPath('deleted', true);

    expect(Webhook::find($webhook->id))->toBeNull();
});
