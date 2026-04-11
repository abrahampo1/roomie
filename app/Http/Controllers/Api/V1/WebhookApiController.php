<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookDeliveryResource;
use App\Http\Resources\WebhookResource;
use App\Jobs\DeliverWebhookJob;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Webhooks\WebhookEvents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebhookApiController extends Controller
{
    /**
     * List the authenticated user's webhooks, most recent first.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $webhooks = Webhook::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(perPage: (int) $request->integer('per_page', 25));

        return WebhookResource::collection($webhooks);
    }

    public function show(Webhook $webhook): WebhookResource
    {
        $this->authorizeWebhook($webhook);

        return new WebhookResource($webhook);
    }

    /**
     * Create a new webhook. Returns 201 with the generated `secret` — this
     * is the only response on the API that ever carries the plaintext
     * secret, so the client MUST store it here.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'starts_with:https://', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string'],
        ]);

        $events = WebhookEvents::normalize($validated['events']);

        if ($events === null) {
            return response()->json([
                'error' => 'invalid_events',
                'message' => 'One or more of the supplied event types are not recognized.',
            ], 422);
        }

        $secret = 'whsec_'.Str::random(40);

        $webhook = Webhook::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $secret,
            'events' => $events,
            'active' => true,
        ]);

        return response()->json([
            'id' => $webhook->id,
            'name' => $webhook->name,
            'url' => $webhook->url,
            'events' => $webhook->events,
            'active' => true,
            'secret' => $secret,
            'created_at' => $webhook->created_at->toIso8601String(),
        ], 201);
    }

    public function update(Request $request, Webhook $webhook): WebhookResource
    {
        $this->authorizeWebhook($webhook);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'url' => ['sometimes', 'url', 'starts_with:https://', 'max:500'],
            'events' => ['sometimes', 'array', 'min:1'],
            'events.*' => ['string'],
            'active' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['events'])) {
            $events = WebhookEvents::normalize($validated['events']);

            if ($events === null) {
                abort(response()->json([
                    'error' => 'invalid_events',
                    'message' => 'One or more of the supplied event types are not recognized.',
                ], 422));
            }

            $validated['events'] = $events;
        }

        // Re-activating a webhook resets the failure counter so a fixed
        // endpoint doesn't auto-disable again on the next single failure.
        if (isset($validated['active']) && $validated['active'] === true && ! $webhook->active) {
            $validated['consecutive_failures'] = 0;
        }

        $webhook->update($validated);

        return new WebhookResource($webhook->fresh());
    }

    /**
     * Rotate the signing secret. Returns 200 with the new plaintext secret.
     * The previous secret is invalid immediately — there is no overlap
     * window, so consumers must update before the next event fires.
     */
    public function rotateSecret(Webhook $webhook): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        $secret = 'whsec_'.Str::random(40);
        $webhook->update(['secret' => $secret]);

        return response()->json([
            'id' => $webhook->id,
            'secret' => $secret,
            'rotated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Fire a synthetic `webhook.test` event at the webhook's URL. Useful
     * for verifying connectivity and signature verification on the
     * consumer side without having to wait for a real campaign event.
     */
    public function sendTest(Webhook $webhook): JsonResponse
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
                    'message' => 'If you can verify the signature on this payload, your integration is wired up correctly.',
                ],
            ],
            'attempt' => 1,
        ]);

        DeliverWebhookJob::dispatch($delivery->id);

        return response()->json([
            'queued' => true,
            'delivery_id' => $delivery->id,
            'event_id' => $eventId,
        ]);
    }

    public function destroy(Webhook $webhook): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        $webhook->delete();

        return response()->json([
            'deleted' => true,
        ]);
    }

    /**
     * Paginated history of deliveries for a webhook. Deliveries are pruned
     * to the last 7 days by the `webhooks:prune-deliveries` scheduled command.
     */
    public function deliveries(Request $request, Webhook $webhook): AnonymousResourceCollection
    {
        $this->authorizeWebhook($webhook);

        $deliveries = $webhook->deliveries()
            ->latest()
            ->paginate(perPage: (int) $request->integer('per_page', 25));

        return WebhookDeliveryResource::collection($deliveries);
    }

    private function authorizeWebhook(Webhook $webhook): void
    {
        if ($webhook->user_id !== Auth::id()) {
            abort(response()->json([
                'error' => 'forbidden',
                'message' => 'This webhook does not belong to your account.',
            ], 403));
        }
    }
}
