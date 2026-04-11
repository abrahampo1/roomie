<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url', 500);
            // HMAC secret — stored encrypted via the model cast. Shown to
            // the user in plaintext exactly once (at create + on rotate).
            $table->text('secret');
            // Array of subscribed event types, or ["*"] for everything.
            $table->json('events');
            $table->boolean('active')->default(true);
            // How many consecutive failed *events* (not attempts) — the
            // DeliverWebhookJob auto-disables the webhook at >= 10.
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
