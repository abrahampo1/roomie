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
use Illuminate\View\View;

class WebhookSettingsController extends Controller
{
    public function index(): View
    {
        $webhooks = Auth::user()
            ->webhooks()
            ->latest()
            ->get();

        return view('settings.webhooks.index', [
            'webhooks' => $webhooks,
            'newSecret' => session('new_webhook_secret'),
            'newWebhookId' => session('new_webhook_id'),
        ]);
    }

    public function create(): View
    {
        return view('settings.webhooks.create', [
            'events' => WebhookEvents::all(),
        ]);
    }

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
                ->route('settings.webhooks.create')
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
            ->route('settings.webhooks.show', $webhook)
            ->with('new_webhook_secret', $secret)
            ->with('new_webhook_id', $webhook->id);
    }

    public function show(Webhook $webhook): View
    {
        $this->authorizeWebhook($webhook);

        $deliveries = $webhook->deliveries()
            ->latest()
            ->limit(25)
            ->get();

        return view('settings.webhooks.show', [
            'webhook' => $webhook,
            'deliveries' => $deliveries,
            'newSecret' => session('new_webhook_secret'),
            'newWebhookId' => session('new_webhook_id'),
        ]);
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'starts_with:https://', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $events = WebhookEvents::normalize($validated['events']);

        if ($events === null) {
            return redirect()
                ->route('settings.webhooks.show', $webhook)
                ->withErrors(['events' => 'Uno o más eventos no son válidos.'])
                ->withInput();
        }

        $updates = [
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => $events,
        ];

        $shouldActivate = $request->boolean('active');
        if ($shouldActivate && ! $webhook->active) {
            $updates['active'] = true;
            $updates['consecutive_failures'] = 0;
        } elseif (! $shouldActivate) {
            $updates['active'] = false;
        }

        $webhook->update($updates);

        return redirect()
            ->route('settings.webhooks.show', $webhook)
            ->with('message', 'Webhook actualizado.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $webhook->delete();

        return redirect()
            ->route('settings.webhooks.index')
            ->with('message', 'Webhook eliminado.');
    }

    public function rotateSecret(Webhook $webhook): RedirectResponse
    {
        $this->authorizeWebhook($webhook);

        $secret = 'whsec_'.Str::random(40);
        $webhook->update(['secret' => $secret]);

        return redirect()
            ->route('settings.webhooks.show', $webhook)
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
            ->route('settings.webhooks.show', $webhook)
            ->with('message', 'Evento de prueba en cola.');
    }

    private function authorizeWebhook(Webhook $webhook): void
    {
        abort_unless($webhook->user_id === Auth::id(), 403);
    }
}
