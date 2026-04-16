<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $staffs = User::query()
            ->where('role', 'staff')
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nik_nip', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('staff.index', [
            'staffs' => $staffs,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('staff.create', [
            'staff' => new User(['status' => 'active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        User::create([
            'name' => $validated['name'],
            'nik_nip' => $validated['nik_nip'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'role' => 'staff',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('staff.index')
            ->with('status', 'Data staff berhasil ditambahkan.');
    }

    public function edit(User $staff): View
    {
        abort_unless($staff->role === 'staff', 404);

        return view('staff.edit', [
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $validated = $request->validate($this->rules($staff->id, false));

        $payload = [
            'name' => $validated['name'],
            'nik_nip' => $validated['nik_nip'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $staff->update($payload);

        return redirect()
            ->route('staff.index')
            ->with('status', 'Data staff berhasil diperbarui.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('status', 'Data staff berhasil dihapus.');
    }

    private function rules(?int $userId = null, bool $requirePassword = true): array
    {
        $passwordRules = $requirePassword
            ? ['required', 'string', 'min:8', 'confirmed']
            : ['nullable', 'string', 'min:8', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'nik_nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'nik_nip')->ignore($userId),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
            'password' => $passwordRules,
        ];
    }
}
