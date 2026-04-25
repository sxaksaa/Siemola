<?php

namespace App\Providers;

use App\Services\BorrowingStatusService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.siemola', function ($view) {
            $user = auth()->user();
            $notifications = collect();
            $notificationCount = 0;

            if ($user?->role === 'staff') {
                $service = app(BorrowingStatusService::class);

                $service->syncLateStatuses();

                $notificationCount = $service->activeLateQuery()->count();
                $notifications = $service->activeLateQuery()
                    ->oldest('due_at')
                    ->limit(5)
                    ->get();
            }

            $view->with([
                'staffNotifications' => $notifications,
                'staffNotificationCount' => $notificationCount,
            ]);
        });
    }
}
