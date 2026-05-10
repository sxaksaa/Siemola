<?php

use App\Http\Controllers\Api\EspLockerController;
use App\Http\Controllers\flutter\FlutterAuthController;
use App\Http\Controllers\flutter\FlutterDashboardController;
use App\Http\Controllers\flutter\HistoryController;
use App\Http\Controllers\flutter\LockerController;
use App\Http\Controllers\flutter\MahasiswaController;
use App\Http\Controllers\flutter\NotificationController;
use App\Http\Controllers\flutter\ProfileController;
use App\Http\Controllers\flutter\StaffController;
use Illuminate\Support\Facades\Route;

Route::post('/tab', [EspLockerController::class, 'tap']);
Route::post('/getStatus', [EspLockerController::class, 'status']);
Route::get('/history', [EspLockerController::class, 'history']);
Route::get('/dataPeminjaman', [HistoryController::class, 'index']);

Route::get('/lockers', [LockerController::class, 'index']);

Route::post('/login', [FlutterAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [FlutterAuthController::class, 'logout']);

    // Locker
    Route::get('/lockers/summary', [LockerController::class, 'summary']);

    // History


    // Dashboard
    Route::get('/dashboard/stats', [FlutterDashboardController::class, 'stats']);

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Mahasiswa & Staff
    Route::get('/mahasiswa', [MahasiswaController::class, 'index']);
    Route::post('/mahasiswa', [MahasiswaController::class, 'store']);
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);

    Route::put('/mahasiswa/{id}', [MahasiswaController::class, 'update']);
    Route::delete('/mahasiswa/{id}', [MahasiswaController::class, 'destroy']);
    Route::put('/staff/{id}', [StaffController::class, 'update']);
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

    // Profile
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});
