<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\Locker;

class BorrowingStatusService
{
    public function syncLateStatuses(): int
    {
        $lateBorrowings = Borrowing::query()
            ->whereNull('returned_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->where('status', '!=', 'late')
            ->get();

        foreach ($lateBorrowings as $borrowing) {
            $borrowing->update(['status' => 'late']);

            Locker::query()
                ->whereKey($borrowing->locker_id)
                ->update(['status' => 'late']);
        }

        $this->syncResolvedLockers();

        return $lateBorrowings->count();
    }

    public function syncResolvedLockers(): void
    {
        Borrowing::query()
            ->whereNotNull('returned_at')
            ->where('status', '!=', 'returned')
            ->update(['status' => 'returned']);

        $activeBorrowedLockerIds = Borrowing::query()
            ->whereNull('returned_at')
            ->where('status', 'borrowed')
            ->pluck('locker_id')
            ->unique();

        if ($activeBorrowedLockerIds->isNotEmpty()) {
            Locker::query()
                ->whereKey($activeBorrowedLockerIds)
                ->where('status', 'available')
                ->update(['status' => 'borrowed']);
        }

        Locker::query()
            ->whereIn('status', ['borrowed', 'late'])
            ->whereDoesntHave('borrowings', function ($query) {
                $query->whereNull('returned_at')
                    ->whereIn('status', ['borrowed', 'late']);
            })
            ->update(['status' => 'available']);
    }

    public function activeLateQuery()
    {
        return Borrowing::query()
            ->with(['student', 'locker'])
            ->whereNull('returned_at')
            ->where(function ($query) {
                $query->where('status', 'late')
                    ->orWhere(function ($dueQuery) {
                        $dueQuery->whereNotNull('due_at')
                            ->where('due_at', '<=', now());
                    });
            });
    }
}
