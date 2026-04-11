<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['email', 'source_campaign_id', 'reason'])]
class EmailUnsubscribe extends Model
{
    public function sourceCampaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'source_campaign_id');
    }

    public static function isBlocked(string $email): bool
    {
        return static::query()->where('email', $email)->exists();
    }
}
