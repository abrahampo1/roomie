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
    'url',
    'secret',
    'events',
    'active',
    'consecutive_failures',
    'last_triggered_at',
    'last_status_code',
])]
#[Hidden(['secret'])]
class Webhook extends Model
{
    protected function casts(): array
    {
        return [
            'events' => 'array',
            'active' => 'boolean',
            'secret' => 'encrypted',
            'last_triggered_at' => 'datetime',
            'consecutive_failures' => 'integer',
            'last_status_code' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    /**
     * Returns true if this webhook subscribes to the given event type —
     * either via a direct match or the `*` wildcard.
     */
    public function subscribesTo(string $eventType): bool
    {
        $events = $this->events ?? [];

        return in_array('*', $events, true) || in_array($eventType, $events, true);
    }
}
