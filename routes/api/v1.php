<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Pengguna\Http\Controllers\PenggunaController;
use App\Modules\Sekolah\Http\Controllers\SekolahController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('sekolah', SekolahController::class);
        Route::apiResource('pengguna', PenggunaController::class);
    });
});
