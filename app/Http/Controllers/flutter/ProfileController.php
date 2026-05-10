<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // ── POST /api/profile ─────────────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'nik_nip'  => 'nullable|string|max:50',
            'phone'    => 'nullable|string|max:30',
        ]);

        $updateData = [
            'name'    => $request->name,
            'email'   => $request->email,
            'nik_nip' => $request->nik_nip,
            'phone'   => $request->phone,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'nik_nip' => $user->nik_nip,
                'email'   => $user->email,
                'role'    => $user->role,
                'phone'   => $user->phone,
                'status'  => $user->status,
            ],
        ]);
    }

    // ── DELETE /api/profile ───────────────────────────────────────────────────
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus']);
    }
}