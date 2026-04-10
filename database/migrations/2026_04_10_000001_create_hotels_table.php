<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('name');
            $table->string('country_id', 2);
            $table->string('brand');
            $table->unsignedTinyInteger('stars');
            $table->unsignedInteger('num_rooms');
            $table->string('city_name');
            $table->string('city_climate');
            $table->decimal('city_avg_temperature', 4, 1);
            $table->string('city_rain_risk');
            $table->boolean('city_beach_flag')->default(false);
            $table->boolean('city_mountain_flag')->default(false);
            $table->string('city_historical_heritage');
            $table->string('city_price_level');
            $table->string('city_gastronomy');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
