<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('lockers')
            ->whereIn('status', ['maintenance', 'offline'])
            ->update(['status' => 'available']);

        DB::statement("ALTER TABLE lockers MODIFY status ENUM('available', 'borrowed', 'late') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lockers MODIFY status ENUM('available', 'borrowed', 'late', 'maintenance', 'offline') NOT NULL DEFAULT 'available'");
    }
};
