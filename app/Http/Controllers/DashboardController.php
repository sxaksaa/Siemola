<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Locker;
use App\Models\Student;
use App\Models\User;
use App\Services\BorrowingStatusService;
use Carbon\CarbonPeriod;
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
                $isDummy = str_starts_with((string) $locker->device_id, 'DUMMY-');
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
                    'is_dummy' => $isDummy,
                ];
            })
            ->all();

        $displayTimezone = config('app.display_timezone', 'Asia/Jakarta');
        $activityStart = now($displayTimezone)->subDays(6)->startOfDay();
        $activityEnd = now($displayTimezone)->endOfDay();

        $activityBuckets = collect(CarbonPeriod::create($activityStart, '1 day', $activityEnd))
            ->mapWithKeys(fn ($date) => [
                $date->toDateString() => [
                    'date' => $date->toDateString(),
                    'label' => $date->format('d M'),
                    'borrowed' => 0,
                    'returned' => 0,
                ],
            ]);

        Borrowing::query()
            ->select(['borrowed_at', 'returned_at'])
            ->where(function ($query) use ($activityStart, $activityEnd) {
                $query
                    ->whereBetween('borrowed_at', [$activityStart->copy()->utc(), $activityEnd->copy()->utc()])
                    ->orWhereBetween('returned_at', [$activityStart->copy()->utc(), $activityEnd->copy()->utc()]);
            })
            ->get()
            ->each(function (Borrowing $borrowing) use ($activityBuckets, $displayTimezone) {
                if ($borrowing->borrowed_at) {
                    $borrowedDate = $borrowing->borrowed_at->copy()->timezone($displayTimezone)->toDateString();

                    if ($activityBuckets->has($borrowedDate)) {
                        $bucket = $activityBuckets->get($borrowedDate);
                        $bucket['borrowed']++;
                        $activityBuckets->put($borrowedDate, $bucket);
                    }
                }

                if ($borrowing->returned_at) {
                    $returnedDate = $borrowing->returned_at->copy()->timezone($displayTimezone)->toDateString();

                    if ($activityBuckets->has($returnedDate)) {
                        $bucket = $activityBuckets->get($returnedDate);
                        $bucket['returned']++;
                        $activityBuckets->put($returnedDate, $bucket);
                    }
                }
            });

        $activityChart = $activityBuckets->values();
        $maxActivityValue = max(
            $activityChart->pluck('borrowed')->merge($activityChart->pluck('returned'))->max() ?? 1,
            1
        );

        $lockerStatusSummary = collect([
            ['label' => 'Tersedia', 'value' => Locker::query()->where('status', 'available')->count(), 'tone' => 'text-emerald-600 bg-emerald-50 ring-emerald-100'],
            ['label' => 'Sedang dipinjam', 'value' => Locker::query()->where('status', 'borrowed')->count(), 'tone' => 'text-rose-600 bg-rose-50 ring-rose-100'],
            ['label' => 'Telat Mengembalikan', 'value' => $borrowingStatusService->activeLateQuery()->count(), 'tone' => 'text-amber-600 bg-amber-50 ring-amber-100'],
        ]);

        $espLocker = Locker::query()
            ->whereNotNull('device_id')
            ->where('device_id', '!=', '')
            ->where('device_id', 'not like', 'DUMMY-%')
            ->get()
            ->sortByDesc(fn (Locker $locker) => ($locker->switch_reported_at ?? $locker->last_ping_at)?->timestamp ?? 0)
            ->first();

        $lastEspSync = $espLocker?->switch_reported_at ?? $espLocker?->last_ping_at;
        $espSyncState = match (true) {
            ! $lastEspSync => 'waiting',
            $lastEspSync->greaterThanOrEqualTo(now()->subMinute()) => 'online',
            $lastEspSync->greaterThanOrEqualTo(now()->subMinutes(5)) => 'recent',
            default => 'stale',
        };

        $espSyncSummary = [
            'locker' => $espLocker?->code ?? '-',
            'device_id' => $espLocker?->device_id ?? '-',
            'state' => $espSyncState,
            'status' => match ($espSyncState) {
                'online' => 'Online',
                'recent' => 'Baru sinkron',
                'stale' => 'Perlu dicek',
                default => 'Menunggu ESP',
            },
            'time' => $lastEspSync
                ? $lastEspSync->copy()->timezone($displayTimezone)->format('d/m/Y H:i:s')
                : 'Belum ada data',
            'synced_at' => $lastEspSync?->copy()->utc()->toIso8601String(),
            'relative' => $lastEspSync
                ? $lastEspSync->copy()->timezone($displayTimezone)->diffForHumans()
                : 'ESP belum pernah mengirim status switch.',
        ];

        return view('dashboard', [
            'stats' => $stats,
            'lockers' => $lockers,
            'isStudentView' => $isStudentView,
            'activityChart' => $activityChart,
            'maxActivityValue' => $maxActivityValue,
            'lockerStatusSummary' => $lockerStatusSummary,
            'espSyncSummary' => $espSyncSummary,
        ]);
    }
}
