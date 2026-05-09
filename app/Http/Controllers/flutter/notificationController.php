<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    // ── GET /api/notifications ────────────────────────────────────────────────
    // Tampilkan borrowings yang masih aktif / terlambat (belum dikembalikan)
    // Tidak perlu tabel terpisah — langsung query dari borrowings
    public function index(): JsonResponse
    {
        $overdue = Borrowing::with(['student', 'locker'])
            ->whereIn('status', ['borrowed', 'late'])
            ->orderBy('borrowed_at')
            ->get()
            ->map(fn($b) => [
                'id'           => $b->id,
                'nim'          => $b->student?->nim ?? '-',
                'nama'         => $b->student?->name ?? '-',
                'no_hp'        => $b->student?->phone ?? '-',
                'nomor_locker' => $b->locker?->code ?? '-',
                'waktu_pinjam' => $b->borrowed_at?->toISOString(),
                'status'       => $b->status,
                // Tidak ada kolom sudah_dibaca di borrowings,
                // gunakan status 'late' sebagai penanda
                'sudah_dibaca' => $b->status === 'borrowed',
            ]);

        return response()->json(['data' => $overdue]);
    }

    // ── PUT /api/notifications/{id}/read ──────────────────────────────────────
    // Update status borrowing jadi 'late' sebagai tanda sudah dinotifikasi
    public function markRead(int $id): JsonResponse
    {
        $borrowing = Borrowing::findOrFail($id);

        // Hanya update jika masih borrowed dan sudah lewat due_at
        if ($borrowing->status === 'borrowed' && $borrowing->due_at && now()->isAfter($borrowing->due_at)) {
            $borrowing->update(['status' => 'late']);
        }

        return response()->json(['message' => 'Notifikasi diproses']);
    }
}