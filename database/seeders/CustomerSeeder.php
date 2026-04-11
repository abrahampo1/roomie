<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Pool of Spanish first names used to derive a deterministic `first_name`
     * per guest so a re-seed always produces the same data.
     *
     * @var array<int, string>
     */
    private const FIRST_NAME_POOL = [
        'Marta', 'Álvaro', 'Lucía', 'Javier', 'Sofía', 'Carlos', 'Elena',
        'Miguel', 'Paula', 'Daniel', 'Ana', 'Jorge', 'Laura', 'David',
        'Carmen', 'Pablo', 'Isabel', 'Andrés', 'Nuria', 'Sergio',
        'Beatriz', 'Raúl', 'Cristina', 'Óscar', 'Natalia', 'Iván',
        'Silvia', 'Adrián', 'Patricia', 'Marcos',
    ];

    public function run(): void
    {
        $csv = file(database_path('data/customer_data_200.csv'));

        foreach ($csv as $i => $line) {
            if ($i === 0) {
                continue;
            }

            $fields = str_getcsv(trim($line), ';', '"');

            if (count($fields) < 15) {
                continue;
            }

            $guestId = (string) $fields[0];

            Customer::updateOrCreate(
                ['guest_id' => $guestId, 'reservation_id' => $fields[11]],
                [
                    'email' => 'guest'.$guestId.'@example.invalid',
                    'first_name' => self::FIRST_NAME_POOL[
                        ((int) $guestId) % count(self::FIRST_NAME_POOL)
                    ],
                    'country_guest' => $fields[1],
                    'gender' => $fields[2],
                    'age_range' => $fields[3],
                    'last_2_years_stays' => (int) $fields[4],
                    'confirmed_reservations' => (int) $fields[5],
                    'num_distinct_hotels' => (int) $fields[6],
                    'confirmed_reservations_adr' => (float) $fields[7],
                    'avg_length_stay' => (float) $fields[8],
                    'avg_booking_leadtime' => (float) $fields[9],
                    'avg_score' => $fields[10] !== '' ? (float) $fields[10] : null,
                    'reservation_id' => $fields[11],
                    'checkin_date' => $fields[12],
                    'checkout_date' => $fields[13],
                    'hotel_external_id' => $fields[14],
                ],
            );
        }
    }
}
