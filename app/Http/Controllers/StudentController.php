<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $students = Student::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('rfid_uid', 'like', "%{$search}%")
                        ->orWhere('study_program', 'like', "%{$search}%")
                        ->orWhere('class_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('students.create', [
            'student' => new Student(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $student = Student::create($validated);

        $this->syncRfidCard($student);

        return redirect()
            ->route('students.index')
            ->with('status', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('students.edit', [
            'student' => $student,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate($this->rules($student->id));

        $student->update($validated);
        $this->syncRfidCard($student);

        return redirect()
            ->route('students.index')
            ->with('status', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('status', 'Data mahasiswa berhasil dihapus.');
    }

    private function rules(?int $studentId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', 'unique:students,nim'.($studentId ? ','.$studentId : '')],
            'rfid_uid' => ['required', 'string', 'max:100', 'unique:students,rfid_uid'.($studentId ? ','.$studentId : '')],
            'study_program' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive,blocked'],
        ];
    }

    private function syncRfidCard(Student $student): void
    {
        RfidCard::query()->updateOrCreate(
            ['user_id' => $student->id],
            ['uid' => $student->rfid_uid],
        );
    }
}
