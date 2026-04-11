<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CampaignRecipient
 */
class CampaignRecipientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'customer_id' => $this->customer_id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'status' => $this->status,
            'opens_count' => (int) $this->opens_count,
            'clicks_count' => (int) $this->clicks_count,
            'attempts_sent' => (int) $this->attempts_sent,
            'timestamps' => [
                'first_opened_at' => $this->first_opened_at?->toIso8601String(),
                'last_opened_at' => $this->last_opened_at?->toIso8601String(),
                'first_clicked_at' => $this->first_clicked_at?->toIso8601String(),
                'last_clicked_at' => $this->last_clicked_at?->toIso8601String(),
                'unsubscribed_at' => $this->unsubscribed_at?->toIso8601String(),
                'converted_at' => $this->converted_at?->toIso8601String(),
                'bounced_at' => $this->bounced_at?->toIso8601String(),
                'last_sent_at' => $this->last_sent_at?->toIso8601String(),
            ],
            'bounce_reason' => $this->bounce_reason,
            // tracking_token is deliberately omitted — it's a per-recipient
            // secret used to generate the pixel/click URLs and should never
            // leak through the public API.
        ];
    }
}
