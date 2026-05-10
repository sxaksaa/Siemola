<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Locker;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class flutterDashboardController extends Controller
{
    // ── GET /api/dashboard/stats ──────────────────────────────────────────────
    public function stats(): JsonResponse
    {
        return response()->json([
            'jumlah_staff'     => User::whereIn('role', ['staff', 'superadmin'])->count(),
            'jumlah_mahasiswa' => Student::where('status', 'active')->count(),
            'jumlah_locker'    => Locker::count(),
            'locker_tersedia'  => Locker::where('status', 'available')->count(),
            'locker_dipinjam'  => Locker::where('status', 'borrowed')->count(),
            'locker_terlambat' => Locker::where('status', 'late')->count(),
            'aktivitas_harian' => $this->getWeeklyActivity(),
        ]);
    }

    private function getWeeklyActivity(): array
    {
        $labels = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $result = [];

        for ($i = 6; $i >= 0; $i--) {
            $date  = now()->subDays($i)->toDateString();
            $dow   = (int) now()->subDays($i)->format('w');
            $result[] = [
                'label'  => $labels[$dow],
                'jumlah' => Borrowing::whereDate('borrowed_at', $date)->count(),
            ];
        }

        return $result;
    }
}