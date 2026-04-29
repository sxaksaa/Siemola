<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('locker_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('rfid_card_id')->constrained('rfid_cards')->cascadeOnDelete();
            $table->foreignId('locker_id')->constrained('lockers')->cascadeOnDelete();
            $table->timestamp('accessed_at')->useCurrent();
            $table->timestamps();
        });

        $now = now();

        DB::table('students')
            ->select(['id', 'rfid_uid'])
            ->whereNotNull('rfid_uid')
            ->orderBy('id')
            ->get()
            ->each(function ($student) use ($now): void {
                DB::table('rfid_cards')->updateOrInsert(
                    ['user_id' => $student->id],
                    [
                        'uid' => $student->rfid_uid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('locker_accesses');
        Schema::dropIfExists('rfid_cards');
    }
};
