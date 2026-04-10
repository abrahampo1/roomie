<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'guest_id', 'country_guest', 'gender', 'age_range',
    'last_2_years_stays', 'confirmed_reservations', 'num_distinct_hotels',
    'confirmed_reservations_adr', 'avg_length_stay', 'avg_booking_leadtime',
    'avg_score', 'reservation_id', 'checkin_date', 'checkout_date',
    'hotel_external_id',
])]
class Customer extends Model
{
    protected function casts(): array
    {
        return [
            'last_2_years_stays' => 'integer',
            'confirmed_reservations' => 'integer',
            'num_distinct_hotels' => 'integer',
            'confirmed_reservations_adr' => 'float',
            'avg_length_stay' => 'float',
            'avg_booking_leadtime' => 'float',
            'avg_score' => 'float',
            'checkin_date' => 'date',
            'checkout_date' => 'date',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_external_id', 'external_id');
    }
}
