<?php

namespace App\Http\Controllers;

use App\Models\Locker;
use App\Models\Student;
use App\Models\User;
use App\Services\BorrowingStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $borrowingStatusService = app(BorrowingStatusService::class);
        $borrowingStatusService->syncLateStatuses();

        $iconMap = [
            'staff' => 'M5 18h14M6 18v-1a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v1M12 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-6 4a3 3 0 0 0-3 3v1m18-1v-1a3 3 0 0 0-3-3m-1-7a3 3 0 1 1-6 0a3 3 0 0 1 6 0Z',
            'students' => 'M16 21v-1.2a3.8 3.8 0 0 0-3.8-3.8H8.8A3.8 3.8 0 0 0 5 19.8V21m11-11a3 3 0 1 1-6 0a3 3 0 0 1 6 0Zm4 8.2V18a3.2 3.2 0 0 0-2.5-3.1m-1.3-7.4a2.8 2.8 0 1 1 0 5.6',
            'locker' => 'M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm0 5h10M10 9v9m4-9v9',
            'available' => 'M8 11V8a4 4 0 1 1 8 0v3m-9 0h10a1 1 0 0 1 1 1v7a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-7a1 1 0 0 1 1-1Z',
            'borrowed' => 'M8 11V8a4 4 0 1 1 8 0v3m-9 0h10a1 1 0 0 1 1 1v7a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-7a1 1 0 0 1 1-1Z',
            'warning' => 'M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94A2 2 0 0 0 22.18 18l-8.47-14.14a2 2 0 0 0-3.42 0Z',
        ];

        $isStudentView = ! Auth::check() || Auth::user()?->role === 'student';

        $staffCount = User::query()->where('role', 'staff')->count();
        $studentCount = Student::query()->count();
        $lockerCount = Locker::query()->count();

        $stats = [
            ['label' => 'Staf Terdaftar', 'value' => number_format($staffCount, 0, ',', '.'), 'icon_path' => $iconMap['staff'], 'accent' => 'violet'],
            ['label' => 'Mahasiswa Terdaftar', 'value' => number_format($studentCount, 0, ',', '.'), 'icon_path' => $iconMap['students'], 'accent' => 'blue'],
            ['label' => 'Loker Terdaftar', 'value' => number_format($lockerCount, 0, ',', '.'), 'icon_path' => $iconMap['locker'], 'accent' => 'green'],
        ];

        $lockers = Locker::query()
            ->orderByRaw('LENGTH(code), code')
            ->get()
            ->map(function (Locker $locker) use ($iconMap) {
                $statusMap = [
                    'available' => ['label' => 'Tersedia', 'state' => 'available', 'icon_path' => $iconMap['available']],
                    'borrowed' => ['label' => 'Sedang dipinjam', 'state' => 'borrowed', 'icon_path' => $iconMap['borrowed']],
                    'late' => ['label' => 'Telat Mengembalikan', 'state' => 'late', 'icon_path' => $iconMap['warning']],
                ];

                $mappedStatus = $statusMap[$locker->status] ?? $statusMap['available'];

                return [
                    'name' => $locker->code,
                    'status' => $mappedStatus['label'],
                    'state' => $mappedStatus['state'],
                    'icon_path' => $mappedStatus['icon_path'],
                ];
            })
            ->all();

        $studyProgramChart = Student::query()
            ->selectRaw('study_program, COUNT(*) as total')
            ->groupBy('study_program')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(function ($item, $index) {
                $colors = [
                    'from-blue-500 to-cyan-400',
                    'from-emerald-500 to-green-400',
                    'from-violet-500 to-fuchsia-400',
                    'from-amber-500 to-orange-400',
                    'from-rose-500 to-pink-400',
                    'from-slate-500 to-slate-400',
                ];

                return [
                    'label' => $item->study_program,
                    'value' => (int) $item->total,
                    'bar' => $colors[$index] ?? 'from-blue-500 to-cyan-400',
                ];
            });

        $maxStudyProgramValue = max($studyProgramChart->pluck('value')->max() ?? 1, 1);

        $lockerStatusSummary = collect([
            ['label' => 'Tersedia', 'value' => Locker::query()->where('status', 'available')->count(), 'tone' => 'text-emerald-600 bg-emerald-50 ring-emerald-100'],
            ['label' => 'Sedang dipinjam', 'value' => Locker::query()->where('status', 'borrowed')->count(), 'tone' => 'text-rose-600 bg-rose-50 ring-rose-100'],
            ['label' => 'Telat Mengembalikan', 'value' => $borrowingStatusService->activeLateQuery()->count(), 'tone' => 'text-amber-600 bg-amber-50 ring-amber-100'],
        ]);

        return view('dashboard', [
            'stats' => $stats,
            'lockers' => $lockers,
            'isStudentView' => $isStudentView,
            'studyProgramChart' => $studyProgramChart,
            'maxStudyProgramValue' => $maxStudyProgramValue,
            'lockerStatusSummary' => $lockerStatusSummary,
        ]);
    }
}
