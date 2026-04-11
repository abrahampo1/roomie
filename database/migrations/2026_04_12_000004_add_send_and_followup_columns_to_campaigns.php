<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('send_enabled')->default(false)->after('status');
            $table->timestamp('sent_at')->nullable()->after('send_enabled');
            $table->boolean('api_key_retained_for_followups')->default(false)->after('api_key');
            $table->timestamp('api_key_retention_expires_at')->nullable()->after('api_key_retained_for_followups');
            $table->boolean('followups_enabled')->default(false)->after('api_key_retention_expires_at');
            $table->unsignedTinyInteger('followup_max_attempts')->default(3)->after('followups_enabled');
            $table->unsignedSmallInteger('followup_cooldown_hours')->default(48)->after('followup_max_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'send_enabled',
                'sent_at',
                'api_key_retained_for_followups',
                'api_key_retention_expires_at',
                'followups_enabled',
                'followup_max_attempts',
                'followup_cooldown_hours',
            ]);
        });
    }
};
