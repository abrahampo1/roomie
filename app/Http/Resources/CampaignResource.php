<?php

namespace App\Http\Resources;

use App\Services\LLM\LlmClientFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Campaign
 */
class CampaignResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'objective' => $this->objective,
            'status' => $this->status,
            'quality_score' => $this->quality_score,
            'aggressiveness' => $this->aggressiveness,
            'manipulation' => $this->manipulation,
            'provider' => [
                'id' => $this->api_provider,
                'label' => $this->api_provider ? LlmClientFactory::label($this->api_provider) : null,
                'base_url' => $this->api_provider === 'custom' ? $this->api_base_url : null,
                'model' => $this->api_provider === 'custom' ? $this->api_model : null,
            ],
            'analysis' => $this->analysis,
            'strategy' => $this->strategy,
            'creative' => $this->creative,
            'audit' => $this->audit,
            'followup_variants' => $this->followup_variants,
            'send' => [
                'enabled' => (bool) $this->send_enabled,
                'sent_at' => $this->sent_at?->toIso8601String(),
            ],
            'followups' => [
                'enabled' => (bool) $this->followups_enabled,
                'max_attempts' => (int) $this->followup_max_attempts,
                'cooldown_hours' => (int) $this->followup_cooldown_hours,
                'key_retained' => (bool) $this->api_key_retained_for_followups,
                'retention_expires_at' => $this->api_key_retention_expires_at?->toIso8601String(),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
