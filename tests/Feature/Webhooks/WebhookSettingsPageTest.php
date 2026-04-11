<?php

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

it('renders the index page for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/webhooks')
        ->assertStatus(200)
        ->assertSee('Webhooks');
});

it('creates a webhook via the form and flashes the secret once', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings/webhooks', [
        'name' => 'Slack',
        'url' => 'https://example.com/slack',
        'events' => ['campaign.completed'],
    ]);

    $webhook = Webhook::first();
    expect($webhook)->not->toBeNull()
        ->and($webhook->user_id)->toBe($user->id)
        ->and($webhook->events)->toBe(['campaign.completed']);

    $response->assertRedirect(route('settings.webhooks.show', $webhook))
        ->assertSessionHas('new_webhook_secret');
});

it('deletes a webhook from the settings UI', function () {
    $user = User::factory()->create();
    $webhook = Webhook::create([
        'user_id' => $user->id,
        'name' => 'Gone',
        'url' => 'https://example.com/gone',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    $this->actingAs($user)
        ->post("/settings/webhooks/{$webhook->id}/delete")
        ->assertRedirect(route('settings.webhooks.index'));

    expect(Webhook::find($webhook->id))->toBeNull();
});

it('forbids accessing another users webhook', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $webhook = Webhook::create([
        'user_id' => $owner->id,
        'name' => 'Theirs',
        'url' => 'https://example.com/theirs',
        'secret' => 'whsec_x',
        'events' => ['*'],
        'active' => true,
    ]);

    $this->actingAs($stranger)
        ->get("/settings/webhooks/{$webhook->id}")
        ->assertStatus(403);
});
