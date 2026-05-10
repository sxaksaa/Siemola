<?php

namespace App\Http\Controllers\flutter;

use App\Http\Controllers\Controller;
use App\Models\RfidCard;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    // ── GET /api/students ─────────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $students = Student::orderBy('name')->get()->map(fn($s) => [
            'id'            => $s->id,
            'name'          => $s->name,
            'nim'           => $s->nim,
            'rfid_uid'      => $s->rfid_uid,
            'study_program' => $s->study_program,
            'class_name'    => $s->class_name,
            'phone'         => $s->phone,
            'status'        => $s->status,
            'last_tapped_at'=> $s->last_tapped_at?->toISOString(),
        ]);

        return response()->json(['data' => $students]);
    }

    // ── POST /api/students ────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|unique:students,nim',
            'rfid_uid'      => 'required|string|unique:students,rfid_uid',
            'study_program' => 'required|string|max:255',
            'class_name'    => 'required|string|max:30',
            'phone'         => 'nullable|string|max:30',
            'status'        => 'nullable|in:active,inactive,blocked',
        ]);

        $student = Student::create([
            'name'          => $request->name,
            'nim'           => $request->nim,
            'rfid_uid'      => strtoupper(trim($request->rfid_uid)),
            'study_program' => $request->study_program,
            'class_name'    => $request->class_name,
            'phone'         => $request->phone,
            'status'        => $request->input('status', 'active'),
        ]);

        $this->syncRfidCard($student);

        return response()->json([
            'message' => 'Mahasiswa berhasil ditambahkan',
            'data'    => $student,
        ], 201);
    }

    // ── PUT /api/students/{id} ────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $student = Student::findOrFail($id);
 
        $request->validate([
            'name'          => 'required|string|max:255',
            'nim'           => 'required|string|unique:students,nim,' . $id,
            'rfid_uid'      => 'required|string|unique:students,rfid_uid,' . $id,
            'study_program' => 'required|string|max:255',
            'class_name'    => 'required|string|max:30',
            'phone'         => 'nullable|string|max:30',
            'status'        => 'nullable|in:active,inactive,blocked',
        ]);
 
        $student->update([
            'name'          => $request->name,
            'nim'           => $request->nim,
            'rfid_uid'      => strtoupper(trim($request->rfid_uid)),
            'study_program' => $request->study_program,
            'class_name'    => $request->class_name,
            'phone'         => $request->phone,
            'status'        => $request->input('status', $student->status),
        ]);
 
        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data'    => $student->fresh(),
        ]);
    }
 
    // ── DELETE /api/students/{id} ─────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);
 
        // Cek apakah masih ada borrowing aktif
        $aktivBorrowing = $student->borrowings()
            ->whereIn('status', ['borrowed', 'late'])
            ->exists();
 
        if ($aktivBorrowing) {
            return response()->json([
                'message' => 'Tidak dapat dihapus — mahasiswa masih meminjam locker',
            ], 422);
        }
 
        $student->delete();
 
        return response()->json(['message' => 'Mahasiswa berhasil dihapus']);
    }

    private function syncRfidCard(Student $student)
    {
        RfidCard::query()->updateOrCreate(
            ['uid' => $student->rfid_uid],
            ['user_id' => $student->id]
        );
    }
}