<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('first_name')->nullable();

            // queued | sending | sent | bounced | failed | unsubscribed | converted
            $table->string('status')->default('queued');

            $table->unsignedInteger('opens_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->text('bounce_reason')->nullable();

            $table->unsignedInteger('attempts_sent')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_followup_not_before')->nullable();

            $table->string('tracking_token', 40)->unique();

            $table->timestamps();
            $table->unique(['campaign_id', 'email']);
            $table->index(['campaign_id', 'status']);
            $table->index(['campaign_id', 'next_followup_not_before']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
