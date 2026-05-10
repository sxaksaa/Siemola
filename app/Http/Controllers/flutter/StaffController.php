<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // ── GET /api/staff ────────────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $staffs = User::whereIn('role', ['staff', 'superadmin'])
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'nik_nip' => $u->nik_nip,
                'email' => $u->email,
                'role' => $u->role,
                'phone' => $u->phone,
                'status' => $u->status,
            ]);

        return response()->json(['data' => $staffs]);
    }

    // ── POST /api/staff ───────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'nik_nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'role' => 'nullable|in:staff,superadmin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nik_nip' => $request->nik_nip,
            'phone' => $request->phone,
            'role' => $request->input('role', 'staff'),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Staff berhasil ditambahkan',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    // ── PUT /api/staff/{id} ───────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'nik_nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'role' => 'nullable|in:staff,superadmin',
            'status' => 'nullable|in:active,inactive',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'nik_nip' => $request->nik_nip,
            'phone' => $request->phone,
            'role' => $request->input('role', $user->role),
            'status' => $request->input('status', $user->status),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Data staff berhasil diperbarui',
            'data' => $user->fresh(),
        ]);
    }

    // ── DELETE /api/staff/{id} ────────────────────────────────────────────────
    public function destroy(int $id, Request $request): JsonResponse
    {
        $user = User::findOrFail($id);

        // Tidak boleh hapus diri sendiri
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri',
            ], 422);
        }

        // Hapus semua token
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Staff berhasil dihapus']);
    }
}