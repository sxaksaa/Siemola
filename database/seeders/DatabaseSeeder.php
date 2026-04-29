<?php

namespace Database\Seeders;

use App\Models\Locker;
use App\Models\RfidCard;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('sessions')->truncate();
        DB::table('password_reset_tokens')->truncate();
        DB::table('users')->truncate();
        DB::table('locker_accesses')->truncate();
        DB::table('borrowings')->truncate();
        DB::table('rfid_cards')->truncate();
        DB::table('students')->truncate();
        DB::table('lockers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        User::query()->create([
            'id' => 1,
            'name' => 'david',
            'nik_nip' => 'ADM-001',
            'email' => 'david@gmail.com',
            'role' => 'admin',
            'phone' => '081234567890',
            'status' => 'active',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'id' => 2,
            'name' => 'silmah',
            'nik_nip' => 'STF-001',
            'email' => 'silmah@gmail.com',
            'role' => 'staff',
            'phone' => '081334567890',
            'status' => 'active',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        $students = [
            ['name' => 'Embun Bening Cantika Dewi', 'nim' => '243140700111004', 'rfid_uid' => '37 DB 7E 5', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4A', 'status' => 'active'],
            ['name' => 'Bagas Pratama', 'nim' => '243140700111005', 'rfid_uid' => 'BD 9 65 F8', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4B', 'status' => 'active'],
            ['name' => 'Salsa Maharani', 'nim' => '243140700111006', 'rfid_uid' => '41 9 65 F8', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4C', 'status' => 'active'],
            ['name' => 'Rian Saputra', 'nim' => '243140700111007', 'rfid_uid' => 'C5 B7 68 F8', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4D', 'status' => 'active'],
            ['name' => 'Nabila Ayuningtyas', 'nim' => '243140700111008', 'rfid_uid' => '84 F7 65 F8', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4E', 'status' => 'active'],
        ];

        foreach ($students as $student) {
            $createdStudent = Student::query()->updateOrCreate(
                ['nim' => $student['nim']],
                $student,
            );

            RfidCard::query()->updateOrCreate(
                ['user_id' => $createdStudent->id],
                ['uid' => $createdStudent->rfid_uid],
            );
        }

        $lockers = [
            ['code' => 'L1', 'name' => 'Loker 1', 'location' => null, 'device_id' => '14:08:08:A6:69:34', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L2', 'name' => 'Loker 2 Dummy', 'location' => null, 'device_id' => 'DUMMY-L2', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L3', 'name' => 'Loker 3 Dummy', 'location' => null, 'device_id' => 'DUMMY-L3', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L4', 'name' => 'Loker 4 Dummy', 'location' => null, 'device_id' => 'DUMMY-L4', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L5', 'name' => 'Loker 5 Dummy', 'location' => null, 'device_id' => 'DUMMY-L5', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L6', 'name' => 'Loker 6 Dummy', 'location' => null, 'device_id' => 'DUMMY-L6', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L7', 'name' => 'Loker 7 Dummy', 'location' => null, 'device_id' => 'DUMMY-L7', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L8', 'name' => 'Loker 8 Dummy', 'location' => null, 'device_id' => 'DUMMY-L8', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L9', 'name' => 'Loker 9 Dummy', 'location' => null, 'device_id' => 'DUMMY-L9', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L10', 'name' => 'Loker 10 Dummy', 'location' => null, 'device_id' => 'DUMMY-L10', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L11', 'name' => 'Loker 11 Dummy', 'location' => null, 'device_id' => 'DUMMY-L11', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
            ['code' => 'L12', 'name' => 'Loker 12 Dummy', 'location' => null, 'device_id' => 'DUMMY-L12', 'status' => 'available', 'last_ping_at' => null, 'switch_state' => 0, 'switch_reported_at' => null],
        ];

        foreach ($lockers as $locker) {
            Locker::query()->updateOrCreate(
                ['code' => $locker['code']],
                $locker,
            );
        }
    }
}
