<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('guest_id')->unique();
            $table->string('country_guest', 2);
            $table->string('gender');
            $table->string('age_range');
            $table->unsignedInteger('last_2_years_stays');
            $table->unsignedInteger('confirmed_reservations');
            $table->unsignedInteger('num_distinct_hotels');
            $table->decimal('confirmed_reservations_adr', 8, 2);
            $table->decimal('avg_length_stay', 5, 2);
            $table->decimal('avg_booking_leadtime', 6, 2);
            $table->decimal('avg_score', 3, 1)->nullable();
            $table->string('reservation_id');
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->string('hotel_external_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
