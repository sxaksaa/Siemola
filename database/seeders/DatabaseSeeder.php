<?php

namespace Database\Seeders;

use App\Models\Locker;
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
            ['name' => 'Embun Bening Cantika Dewi', 'nim' => '243140700111004', 'rfid_uid' => '12345678910111213', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4A', 'status' => 'active'],
            ['name' => 'Bagas Pratama', 'nim' => '243140700111005', 'rfid_uid' => '12345678910111214', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4B', 'status' => 'active'],
            ['name' => 'Salsa Maharani', 'nim' => '243140700111006', 'rfid_uid' => '12345678910111215', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4C', 'status' => 'active'],
            ['name' => 'Rian Saputra', 'nim' => '243140700111007', 'rfid_uid' => '12345678910111216', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4D', 'status' => 'active'],
            ['name' => 'Nabila Ayuningtyas', 'nim' => '243140700111008', 'rfid_uid' => '12345678910111217', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4E', 'status' => 'active'],
            ['name' => 'Galih Permana', 'nim' => '243140700111009', 'rfid_uid' => '12345678910111218', 'study_program' => 'Teknologi Informasi', 'class_name' => 'T4F', 'status' => 'active'],
        ];

        foreach ($students as $student) {
            Student::query()->updateOrCreate(
                ['nim' => $student['nim']],
                $student,
            );
        }

        $lockers = [
            ['code' => 'L1', 'name' => 'Locker 1', 'location' => 'Lab TI A', 'device_id' => 'ESP32-L1', 'status' => 'borrowed', 'last_ping_at' => now()],
            ['code' => 'L2', 'name' => 'Locker 2', 'location' => 'Lab TI A', 'device_id' => 'ESP32-L2', 'status' => 'available', 'last_ping_at' => now()],
            ['code' => 'L3', 'name' => 'Locker 3', 'location' => 'Lab TI A', 'device_id' => 'ESP32-L3', 'status' => 'available', 'last_ping_at' => now()],
            ['code' => 'L4', 'name' => 'Locker 4', 'location' => 'Lab TI B', 'device_id' => 'ESP32-L4', 'status' => 'borrowed', 'last_ping_at' => now()],
            ['code' => 'L5', 'name' => 'Locker 5', 'location' => 'Lab TI B', 'device_id' => 'ESP32-L5', 'status' => 'available', 'last_ping_at' => now()],
            ['code' => 'L6', 'name' => 'Locker 6', 'location' => 'Lab TI B', 'device_id' => 'ESP32-L6', 'status' => 'late', 'last_ping_at' => now()],
            ['code' => 'L7', 'name' => 'Locker 7', 'location' => 'Workshop Elektro', 'device_id' => 'ESP32-L7', 'status' => 'available', 'last_ping_at' => now()],
            ['code' => 'L8', 'name' => 'Locker 8', 'location' => 'Workshop Elektro', 'device_id' => 'ESP32-L8', 'status' => 'maintenance', 'last_ping_at' => now()],
            ['code' => 'L9', 'name' => 'Locker 9', 'location' => 'Perpustakaan', 'device_id' => 'ESP32-L9', 'status' => 'offline', 'last_ping_at' => now()],
            ['code' => 'L10', 'name' => 'Locker 10', 'location' => 'Perpustakaan', 'device_id' => 'ESP32-L10', 'status' => 'available', 'last_ping_at' => now()],
            ['code' => 'L11', 'name' => 'Locker 11', 'location' => 'Ruang Multimedia', 'device_id' => 'ESP32-L11', 'status' => 'available', 'last_ping_at' => now()],
            ['code' => 'L12', 'name' => 'Locker 12', 'location' => 'Ruang Multimedia', 'device_id' => 'ESP32-L12', 'status' => 'borrowed', 'last_ping_at' => now()],
        ];

        foreach ($lockers as $locker) {
            Locker::query()->updateOrCreate(
                ['code' => $locker['code']],
                $locker,
            );
        }
    }
}
