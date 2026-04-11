<?php

namespace App\Http\Resources;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Webhook
 */
class WebhookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'events' => $this->events,
            'active' => (bool) $this->active,
            'consecutive_failures' => (int) $this->consecutive_failures,
            'last_triggered_at' => $this->last_triggered_at?->toIso8601String(),
            'last_status_code' => $this->last_status_code,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // `secret` is deliberately omitted — only the one-shot
            // response from create/rotate carries it in plaintext.
        ];
    }
}
