<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'objective',
    'aggressiveness',
    'manipulation',
    'status',
    'send_enabled',
    'sent_at',
    'analysis',
    'strategy',
    'creative',
    'followup_variants',
    'audit',
    'quality_score',
    'api_provider',
    'api_key',
    'api_key_retained_for_followups',
    'api_key_retention_expires_at',
    'followups_enabled',
    'followup_max_attempts',
    'followup_cooldown_hours',
    'api_base_url',
    'api_model',
])]
#[Hidden(['api_key'])]
class Campaign extends Model
{
    protected function casts(): array
    {
        return [
            'analysis' => 'array',
            'strategy' => 'array',
            'creative' => 'array',
            'followup_variants' => 'array',
            'audit' => 'array',
            'quality_score' => 'integer',
            'aggressiveness' => 'integer',
            'manipulation' => 'integer',
            'api_key' => 'encrypted',
            'send_enabled' => 'boolean',
            'sent_at' => 'datetime',
            'api_key_retained_for_followups' => 'boolean',
            'api_key_retention_expires_at' => 'datetime',
            'followups_enabled' => 'boolean',
            'followup_max_attempts' => 'integer',
            'followup_cooldown_hours' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
