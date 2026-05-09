<?php

use App\Http\Controllers\Api\EspLockerController;
use App\Http\Controllers\flutter\flutterAuthController;
use App\Http\Controllers\flutter\flutterDashboardController;
use App\Http\Controllers\flutter\historyController;
use App\Http\Controllers\flutter\lockerController;
use App\Http\Controllers\flutter\mahasiswaController;
use App\Http\Controllers\flutter\notificationController;
use App\Http\Controllers\flutter\profileController;
use App\Http\Controllers\flutter\staffController;
use Illuminate\Support\Facades\Route;

Route::post('/tab', [EspLockerController::class, 'tap']);
Route::post('/getStatus', [EspLockerController::class, 'status']);
Route::get('/history', [EspLockerController::class, 'history']);
Route::get('/dataPeminjaman', [historyController::class, 'index']);

Route::get('/lockers', [lockerController::class, 'index']);

Route::post('/login', [flutterAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [flutterAuthController::class, 'logout']);

    // Locker
    Route::get('/lockers/summary', [lockerController::class, 'summary']);

    // History


    // Dashboard
    Route::get('/dashboard/stats', [flutterDashboardController::class, 'stats']);

    // Notifikasi
    Route::get('/notifications', [notificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [notificationController::class, 'markRead']);

    // Mahasiswa & Staff
    Route::get('/mahasiswa', [mahasiswaController::class, 'index']);
    Route::post('/mahasiswa', [mahasiswaController::class, 'store']);
    Route::get('/staff', [staffController::class, 'index']);
    Route::post('/staff', [staffController::class, 'store']);

    Route::put('/mahasiswa/{id}', [mahasiswaController::class, 'update']);
    Route::delete('/mahasiswa/{id}', [mahasiswaController::class, 'destroy']);
    Route::put('/staff/{id}', [staffController::class, 'update']);
    Route::delete('/staff/{id}', [staffController::class, 'destroy']);

    // Profile
    Route::post('/profile', [profileController::class, 'update']);
    Route::delete('/profile', [profileController::class, 'destroy']);
});
