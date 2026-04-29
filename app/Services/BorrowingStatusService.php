<?php

namespace App\Services;

use App\Models\Borrowing;

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
