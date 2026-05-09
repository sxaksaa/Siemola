<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    // ── GET /api/borrowings ───────────────────────────────────────────────────
    // Filter: ?tanggal=2025-01-07&study_program=Teknik+Informatika&status=borrowed
    public function index(Request $request): JsonResponse
    {
        $query = Borrowing::with(['student', 'locker'])
                          ->orderBy('borrowed_at', 'desc');

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('borrowed_at', $request->tanggal);
        }

        // Filter prodi / study_program
        if ($request->filled('study_program')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('study_program', $request->study_program);
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->get()->map(fn($b) => [
            'id'            => $b->id,
            'borrowed_at'   => $b->borrowed_at?->toDateString(),
            'due_at'    => $b->borrowed_at?->toISOString(),
            'returned_at'   => $b->returned_at?->toISOString(),
            'student_name'  => $b->student?->name ?? '-',
            'student_nim'   => $b->student?->nim ?? '-',
            'study_program' => $b->student?->study_program ?? '-',
            'locker_code'   => $b->locker?->code ?? '-',
            'locker_name'   => $b->locker?->name ?? '-',
            'status'        => $b->status,
        ]);

        return response()->json(['data' => $results]);
    }
}