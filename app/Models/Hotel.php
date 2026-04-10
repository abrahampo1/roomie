<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'external_id', 'name', 'country_id', 'brand', 'stars', 'num_rooms',
    'city_name', 'city_climate', 'city_avg_temperature', 'city_rain_risk',
    'city_beach_flag', 'city_mountain_flag', 'city_historical_heritage',
    'city_price_level', 'city_gastronomy',
])]
class Hotel extends Model
{
    protected function casts(): array
    {
        return [
            'stars' => 'integer',
            'num_rooms' => 'integer',
            'city_avg_temperature' => 'float',
            'city_beach_flag' => 'boolean',
            'city_mountain_flag' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'hotel_external_id', 'external_id');
    }
}
