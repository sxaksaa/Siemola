<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('borrowings:sync-late', function () {
    $count = app(\App\Services\BorrowingStatusService::class)->syncLateStatuses();

    $this->info("Synced {$count} late borrowing(s).");
})->purpose('Mark overdue borrowings and lockers as late');

Schedule::command('borrowings:sync-late')
    ->dailyAt('17:00')
    ->timezone(config('app.display_timezone', 'Asia/Jakarta'));
