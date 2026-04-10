<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $csv = file(database_path('data/hotel_data.csv'));

        foreach ($csv as $i => $line) {
            if ($i === 0) {
                continue;
            }

            $fields = str_getcsv(trim($line), ';', '"');

            if (count($fields) < 15) {
                continue;
            }

            Hotel::updateOrCreate(
                ['external_id' => $fields[0]],
                [
                    'name' => $fields[1],
                    'country_id' => $fields[2],
                    'brand' => $fields[3],
                    'stars' => (int) $fields[4],
                    'num_rooms' => (int) $fields[5],
                    'city_name' => $fields[6],
                    'city_climate' => $fields[7],
                    'city_avg_temperature' => (float) $fields[8],
                    'city_rain_risk' => $fields[9],
                    'city_beach_flag' => $fields[10] === 'YES',
                    'city_mountain_flag' => $fields[11] === 'YES',
                    'city_historical_heritage' => $fields[12],
                    'city_price_level' => $fields[13],
                    'city_gastronomy' => $fields[14],
                ],
            );
        }
    }
}
