<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $students = [
            '243140700111004' => '37 DB 7E 5',
            '243140700111005' => 'BD 9 65 F8',
            '243140700111006' => '41 9 65 F8',
            '243140700111007' => 'C5 B7 68 F8',
            '243140700111008' => '84 F7 65 F8',
        ];

        foreach ($students as $nim => $uid) {
            DB::table('students')
                ->where('nim', $nim)
                ->update(['rfid_uid' => $uid]);
        }

        for ($index = 1; $index <= 12; $index++) {
            DB::table('lockers')->updateOrInsert(
                ['code' => 'L'.$index],
                [
                    'name' => $index === 1 ? 'Loker 1' : 'Loker '.$index.' Dummy',
                    'location' => null,
                    'device_id' => $index === 1 ? '14:08:08:A6:69:34' : 'DUMMY-L'.$index,
                    'status' => 'available',
                    'last_ping_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        //
    }
};
