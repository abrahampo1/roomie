<?php

namespace App\Http\Controllers;

use App\Jobs\DeliverWebhookJob;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Webhooks\WebhookEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebhookSettingsController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'starts_with:https://', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string'],
        ]);

        $events = WebhookEvents::normalize($validated['events']);

        if ($events === null) {
            return redirect()
                ->route('settings.api-token.show')
                ->withErrors(['events' => 'Uno o más eventos no son válidos.'])
                ->withInput();
        }

        $secret = 'whsec_'.Str::random(40);

        $webhook = Auth::user()->webhooks()->create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $secret,
            'events' => $events,
            'active' => true,
        ]);

        return redirect()
            ->route('settings.api-token.show')
            ->with('new_webhook_secret', $secret)
            ->with('new_webhook_id', $webhook->id);
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $shouldActivate = $request->boolean('active');

        $updates = ['active' => $shouldActivate];

        // Re-activating a webhook resets the failure counter so a fixed
        // endpoint doesn't auto-disable again on the next single failure.
        if ($shouldActivate && ! $webhook->active) {
            $updates['consecutive_failures'] = 0;
        }

        $webhook->update($updates);

        return redirect()
            ->route('settings.api-token.show')
            ->with('message', $shouldActivate ? 'Webhook reactivado.' : 'Webhook desactivado.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $webhook->delete();

        return redirect()
            ->route('settings.api-token.show')
            ->with('message', 'Webhook eliminado.');
    }

    public function rotateSecret(Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $secret = 'whsec_'.Str::random(40);
        $webhook->update(['secret' => $secret]);

        return redirect()
            ->route('settings.api-token.show')
            ->with('new_webhook_secret', $secret)
            ->with('new_webhook_id', $webhook->id);
    }

    public function sendTest(Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $eventId = (string) Str::uuid();

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event_type' => 'webhook.test',
            'event_id' => $eventId,
            'payload' => [
                'id' => $eventId,
                'type' => 'webhook.test',
                'created' => now()->timestamp,
                'data' => [
                    'message' => 'Si puedes verificar la firma de este payload, tu integración está lista.',
                ],
            ],
            'attempt' => 1,
        ]);

        DeliverWebhookJob::dispatch($delivery->id);

        return redirect()
            ->route('settings.api-token.show')
            ->with('message', 'Evento de prueba en cola.');
    }

    private function authorizeWebhook(Webhook $webhook): void
    {
        abort_unless($webhook->user_id === Auth::id(), 403);
    }
}
