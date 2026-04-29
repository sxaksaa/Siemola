<?php

namespace App\Http\Controllers;

use App\Models\Locker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LockerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $lockers = Locker::query()
            ->with([
                'latestLockerAccess.student',
                'borrowings' => function ($query) {
                    $query->with('student')
                        ->whereNull('returned_at')
                        ->latest('borrowed_at');
                },
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('device_id', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('switch_state', 'like', "%{$search}%")
                        ->orWhereHas('borrowings', function ($borrowingQuery) use ($search) {
                            $borrowingQuery
                                ->whereNull('returned_at')
                                ->whereHas('student', function ($studentQuery) use ($search) {
                                    $studentQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->orderByRaw('LENGTH(code), code')
            ->paginate(10)
            ->withQueryString();

        return view('lockers.index', [
            'lockers' => $lockers,
            'search' => $search,
            'displayTimezone' => config('app.display_timezone', 'Asia/Jakarta'),
        ]);
    }

    public function create(): View
    {
        return view('lockers.create', [
            'locker' => new Locker(['status' => 'available']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $validated['location'] = null;
        $validated['status'] = 'available';
        $validated['last_ping_at'] = null;
        $validated['switch_state'] = null;
        $validated['switch_reported_at'] = null;

        Locker::create($validated);

        return redirect()
            ->route('lockers.index')
            ->with('status', 'Data loker berhasil ditambahkan.');
    }

    public function edit(Locker $locker): View
    {
        return view('lockers.edit', [
            'locker' => $locker,
        ]);
    }

    public function update(Request $request, Locker $locker): RedirectResponse
    {
        $validated = $request->validate($this->rules($locker->id));

        $validated['location'] = null;

        $locker->update($validated);

        return redirect()
            ->route('lockers.index')
            ->with('status', 'Data loker berhasil diperbarui.');
    }

    public function destroy(Locker $locker): RedirectResponse
    {
        $locker->delete();

        return redirect()
            ->route('lockers.index')
            ->with('status', 'Data loker berhasil dihapus.');
    }

    private function rules(?int $lockerId = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lockers', 'code')->ignore($lockerId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'device_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('lockers', 'device_id')->ignore($lockerId),
            ],
        ];
    }
}
