<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lockers', function (Blueprint $table) {
            if (! Schema::hasColumn('lockers', 'switch_state')) {
                $table->unsignedTinyInteger('switch_state')->nullable();
            }

            if (! Schema::hasColumn('lockers', 'switch_reported_at')) {
                $table->timestamp('switch_reported_at')->nullable();
            }
        });

        DB::table('lockers')
            ->whereNull('switch_state')
            ->update([
                'switch_state' => DB::raw("CASE WHEN status = 'available' THEN 0 ELSE 1 END"),
            ]);
    }

    public function down(): void
    {
        Schema::table('lockers', function (Blueprint $table) {
            if (Schema::hasColumn('lockers', 'switch_reported_at')) {
                $table->dropColumn('switch_reported_at');
            }

            if (Schema::hasColumn('lockers', 'switch_state')) {
                $table->dropColumn('switch_state');
            }
        });
    }
};
