<?php

namespace App\Http\Controllers;

use App\Models\WebhookDelivery;
use App\Services\Webhooks\WebhookEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

        $webhooks = $user->webhooks()->latest()->get();

        $deliveries = WebhookDelivery::query()
            ->whereIn('webhook_id', $webhooks->pluck('id'))
            ->with('webhook:id,name')
            ->latest()
            ->limit(25)
            ->get();

        return view('settings.api-token', [
            'user' => $user,
            // $newToken is flashed to the session exactly once when a token
            // is generated. After the first render it disappears forever.
            'newToken' => session('new_token'),
            'webhooks' => $webhooks,
            'deliveries' => $deliveries,
            'events' => WebhookEvents::all(),
            'campaignEvents' => WebhookEvents::CAMPAIGN_EVENTS,
            'recipientEvents' => WebhookEvents::RECIPIENT_EVENTS,
            'newWebhookSecret' => session('new_webhook_secret'),
            'newWebhookId' => session('new_webhook_id'),
        ]);
    }

    public function generate(): RedirectResponse
    {
        $plain = Auth::user()->generateApiToken();

        return redirect()
            ->route('settings.api-token.show')
            ->with('new_token', $plain);
    }

    public function revoke(): RedirectResponse
    {
        Auth::user()->revokeApiToken();

        return redirect()
            ->route('settings.api-token.show')
            ->with('message', 'Token revocado. Cualquier integración que lo estuviera usando ya no tiene acceso.');
    }
}
