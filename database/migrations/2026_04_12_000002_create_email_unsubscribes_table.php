<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->foreignId('source_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->string('reason')->nullable(); // user_clicked | bounced | admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_unsubscribes');
    }
};
