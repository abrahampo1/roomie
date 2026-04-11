<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('guest_id');
            $table->string('first_name')->nullable()->after('email');
            $table->index('email');
        });

        // Backfill existing rows with RFC 2606 reserved addresses so nothing
        // accidentally reaches a real mailbox during a re-seed.
        DB::table('customers')
            ->whereNull('email')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('customers')->where('id', $row->id)->update([
                        'email' => 'guest'.$row->guest_id.'@example.invalid',
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn(['email', 'first_name']);
        });
    }
};
