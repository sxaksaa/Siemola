<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\Student;
use App\Models\Borrowing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LockerController extends Controller
{
    // ── GET /api/lockers ─────────────────────────────────────────────────────
    // Dipanggil Flutter — ambil semua locker beserta info peminjam aktif
    public function index(): JsonResponse
    {
        $lockers = Locker::orderBy('id')->get()->map(function ($locker) {
            // Cari borrowing aktif untuk locker ini
            $activeBorrowing = Borrowing::with('student')
                ->where('locker_id', $locker->id)
                ->whereIn('status', ['borrowed', 'late'])
                ->latest('borrowed_at')
                ->first();

            return [
                'id'             => $locker->id,
                'code'           => $locker->code,
                'name'           => $locker->name,
                'location'       => $locker->location,
                'device_id'      => $locker->device_id,
                'status'         => $locker->status,
                'last_ping_at'   => $locker->last_ping_at?->toISOString(),
                'switch_state'   => $locker->switch_state,
                // Info peminjam (null kalau tersedia)
                'nama_peminjam'  => $activeBorrowing?->student?->name,
                'nim_peminjam'   => $activeBorrowing?->student?->nim,
                'waktu_pinjam'   => $activeBorrowing?->borrowed_at?->toISOString(),
            ];
        });

        return response()->json(['data' => $lockers]);
    }

    // ── GET /api/lockers/summary ──────────────────────────────────────────────
    public function summary(): JsonResponse
    {
        return response()->json([
            'tersedia'  => Locker::where('status', 'available')->count(),
            'dipinjam'  => Locker::where('status', 'borrowed')->count(),
            'terlambat' => Locker::where('status', 'late')->count(),
            'total'     => Locker::count(),
        ]);
    }

    // ── POST /api/tab ─────────────────────────────────────────────────────────
    // Dipanggil ESP32 saat RFID di-tap
    public function tab(Request $request): JsonResponse
    {
        $request->validate([
            'uid'       => 'required|string',
            'device_id' => 'required|string',
        ]);

        $uid      = strtoupper(trim($request->uid));
        $deviceId = $request->device_id;

        // Cari locker dari device_id ESP32
        $locker = Locker::where('device_id', $deviceId)->first();
        if (!$locker) {
            return response()->json(['status' => 'gagal', 'message' => 'Locker tidak terdaftar']);
        }

        // Cari student dari rfid_uid
        $student = Student::where('rfid_uid', $uid)
                          ->where('status', 'active')
                          ->first();
        if (!$student) {
            return response()->json(['status' => 'gagal', 'message' => 'UID tidak dikenal']);
        }

        // Update last_tapped_at
        $student->update(['last_tapped_at' => now()]);

        if ($locker->status === 'available') {
            // ── Pinjam ──────────────────────────────────────────────────────
            $locker->update([
                'status'      => 'borrowed',
                'last_ping_at'=> now(),
            ]);

            Borrowing::create([
                'student_id'       => $student->id,
                'locker_id'        => $locker->id,
                'borrowed_rfid_uid'=> $uid,
                'borrowed_at'      => now(),
                'due_at'           => now()->setTime(17, 0, 0), // due jam 17.00
                'status'           => 'borrowed',
            ]);

        } else {
            // ── Kembalikan ───────────────────────────────────────────────────
            // Pastikan yang tap adalah peminjam aktif
            $activeBorrowing = Borrowing::where('locker_id', $locker->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['borrowed', 'late'])
                ->latest('borrowed_at')
                ->first();

            if (!$activeBorrowing) {
                return response()->json(['status' => 'gagal', 'message' => 'Kamu bukan peminjam locker ini']);
            }

            $returnStatus = now()->isAfter($activeBorrowing->due_at) ? 'late' : 'returned';

            $activeBorrowing->update([
                'returned_at' => now(),
                'status'      => $returnStatus,
            ]);

            $locker->update([
                'status'      => 'available',
                'last_ping_at'=> now(),
            ]);
        }

        return response()->json(['status' => 'success', 'nama' => $student->name]);
    }

    // ── POST /api/getStatus ───────────────────────────────────────────────────
    // Dipanggil ESP32 setiap 10 detik — sync limit switch
    public function getStatus(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $locStatus = $request->input('locstatus', $request->input('status_locker'));
        $locker    = Locker::where('device_id', $request->device_id)->first();

        if ($locker) {
            $updateData = [
                'switch_state'        => $locStatus,
                'switch_reported_at'  => now(),
                'last_ping_at'        => now(),
            ];

            // Jika limit switch bilang kosong (0) tapi status locker masih borrowed
            // → anggap barang sudah diambil paksa
            if ($locStatus == 0 && in_array($locker->status, ['borrowed', 'late'])) {
                $updateData['status'] = 'available';

                Borrowing::where('locker_id', $locker->id)
                    ->whereIn('status', ['borrowed', 'late'])
                    ->latest('borrowed_at')
                    ->first()
                    ?->update(['returned_at' => now(), 'status' => 'returned']);
            }

            $locker->update($updateData);
        }

        // Kembalikan status dalam format int untuk ESP32 (0=tersedia, 1=terpakai)
        $statusInt = ($locker && $locker->status !== 'available') ? 1 : 0;
        return response()->json(['status' => $statusInt]);
    }
}