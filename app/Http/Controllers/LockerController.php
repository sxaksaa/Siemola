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
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('device_id', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('lockers.index', [
            'lockers' => $lockers,
            'search' => $search,
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
            'location' => ['nullable', 'string', 'max:255'],
            'device_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('lockers', 'device_id')->ignore($lockerId),
            ],
            'status' => ['required', 'in:available,borrowed,late,maintenance,offline'],
            'last_ping_at' => ['nullable', 'date'],
        ];
    }
}
