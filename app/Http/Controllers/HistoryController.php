<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('history.index', [
            'borrowings' => $this->query($filters)
                ->latest('borrowed_at')
                ->paginate(10)
                ->withQueryString(),
            'studyPrograms' => $this->studyPrograms(),
            'filters' => $filters,
            'displayTimezone' => $this->displayTimezone(),
        ]);
    }

    public function export(Request $request): View
    {
        $filters = $this->filters($request);

        return view('history.export', [
            'borrowings' => $this->query($filters)
                ->oldest('borrowed_at')
                ->get(),
            'filters' => $filters,
            'displayTimezone' => $this->displayTimezone(),
        ]);
    }

    private function query(array $filters): Builder
    {
        return Borrowing::query()
            ->with(['student', 'locker'])
            ->where('borrowed_at', '>=', $filters['start_at'])
            ->where('borrowed_at', '<', $filters['end_at'])
            ->when($filters['study_program'] !== '', function (Builder $query) use ($filters) {
                $query->whereHas('student', function (Builder $studentQuery) use ($filters) {
                    $studentQuery->where('study_program', $filters['study_program']);
                });
            })
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $query->where(function (Builder $searchQuery) use ($filters) {
                    $searchQuery
                        ->where('borrowed_rfid_uid', 'like', "%{$filters['search']}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($filters) {
                            $studentQuery
                                ->where('name', 'like', "%{$filters['search']}%")
                                ->orWhere('nim', 'like', "%{$filters['search']}%")
                                ->orWhere('study_program', 'like', "%{$filters['search']}%");
                        })
                        ->orWhereHas('locker', function (Builder $lockerQuery) use ($filters) {
                            $lockerQuery
                                ->where('code', 'like', "%{$filters['search']}%")
                                ->orWhere('name', 'like', "%{$filters['search']}%");
                        });
                });
            });
    }

    private function filters(Request $request): array
    {
        $timezone = $this->displayTimezone();
        $today = now($timezone)->toDateString();
        $startDate = $this->dateValue($request->query('start_date'), $today);
        $endDate = $this->dateValue($request->query('end_date'), now($timezone)->addDay()->toDateString());

        if (Carbon::parse($endDate, $timezone)->lessThanOrEqualTo(Carbon::parse($startDate, $timezone))) {
            $endDate = Carbon::parse($startDate, $timezone)->addDay()->toDateString();
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_at' => Carbon::parse($startDate, $timezone)->startOfDay()->utc(),
            'end_at' => Carbon::parse($endDate, $timezone)->startOfDay()->utc(),
            'study_program' => trim((string) $request->query('study_program', '')),
            'search' => trim((string) $request->query('search', '')),
        ];
    }

    private function dateValue(mixed $value, string $fallback): string
    {
        if (! is_string($value) || $value === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function studyPrograms()
    {
        return Student::query()
            ->select('study_program')
            ->whereNotNull('study_program')
            ->distinct()
            ->orderBy('study_program')
            ->pluck('study_program');
    }

    private function displayTimezone(): string
    {
        return config('app.display_timezone', 'Asia/Jakarta');
    }
}
