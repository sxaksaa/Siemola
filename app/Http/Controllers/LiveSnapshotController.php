<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Locker;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSnapshotController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $path = '/'.trim((string) $request->query('path', '/'), '/');

        $parts = match ($path) {
            '/', '/dashboard' => [
                'students' => $this->version(Student::query()),
                'staff' => $this->version(User::query()->where('role', 'staff')),
                'lockers' => $this->version(Locker::query()),
                'borrowings' => $this->version(Borrowing::query()),
            ],
            '/history' => [
                'borrowings' => $this->version(Borrowing::query()),
                'students' => $this->version(Student::query()),
                'lockers' => $this->version(Locker::query()),
            ],
            '/lockers' => [
                'lockers' => $this->version(Locker::query()),
                'borrowings' => $this->version(Borrowing::query()->whereNull('returned_at')),
                'students' => $this->version(Student::query()),
            ],
            '/students' => [
                'students' => $this->version(Student::query()),
                'rfid' => $this->version(\App\Models\RfidCard::query()),
            ],
            '/staff' => [
                'staff' => $this->version(User::query()->where('role', 'staff')),
            ],
            default => [
                'path' => $path,
            ],
        };

        return response()->json([
            'path' => $path,
            'hash' => sha1(json_encode($parts)),
        ]);
    }

    private function version(Builder $query): array
    {
        return [
            'count' => (clone $query)->count(),
            'latest' => (string) ((clone $query)->max('updated_at') ?? ''),
        ];
    }
}
