<?php

use App\Http\Controllers\Api\EspLockerController;
use Illuminate\Support\Facades\Route;

Route::post('/tab', [EspLockerController::class, 'tap']);
Route::post('/getStatus', [EspLockerController::class, 'status']);
Route::get('/history', [EspLockerController::class, 'history']);
