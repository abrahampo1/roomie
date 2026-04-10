<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('objective');
            $table->string('status')->default('pending');
            $table->json('analysis')->nullable();
            $table->json('strategy')->nullable();
            $table->json('creative')->nullable();
            $table->json('audit')->nullable();
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
