<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedTinyInteger('aggressiveness')->default(2)->after('objective');
            $table->unsignedTinyInteger('persuasion_patterns')->default(2)->after('aggressiveness');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['aggressiveness', 'persuasion_patterns']);
        });
    }
};
