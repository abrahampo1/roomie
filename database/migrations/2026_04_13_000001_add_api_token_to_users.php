<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // We store the SHA-256 hash of the plain token, not the token
            // itself. Indexed so lookups in the middleware are O(1).
            $table->string('api_token', 64)->nullable()->unique()->after('password');
            $table->timestamp('api_token_created_at')->nullable()->after('api_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['api_token']);
            $table->dropColumn(['api_token', 'api_token_created_at']);
        });
    }
};
