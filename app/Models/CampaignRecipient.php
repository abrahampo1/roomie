<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'campaign_id',
    'customer_id',
    'email',
    'first_name',
    'status',
    'opens_count',
    'clicks_count',
    'first_opened_at',
    'last_opened_at',
    'first_clicked_at',
    'last_clicked_at',
    'unsubscribed_at',
    'converted_at',
    'bounced_at',
    'bounce_reason',
    'attempts_sent',
    'last_sent_at',
    'next_followup_not_before',
    'tracking_token',
])]
class CampaignRecipient extends Model
{
    protected function casts(): array
    {
        return [
            'opens_count' => 'integer',
            'clicks_count' => 'integer',
            'attempts_sent' => 'integer',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'first_clicked_at' => 'datetime',
            'last_clicked_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'converted_at' => 'datetime',
            'bounced_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'next_followup_not_before' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $recipient) {
            if (empty($recipient->tracking_token)) {
                $recipient->tracking_token = (string) Str::random(40);
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function isUnsubscribed(): bool
    {
        return $this->status === 'unsubscribed';
    }
}
