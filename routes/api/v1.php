<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Pengguna\Http\Controllers\PenggunaController;
use App\Modules\Perencanaan\Http\Controllers\SesiSupervisiController;
use App\Modules\Sekolah\Http\Controllers\SekolahController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('permission:sekolah.manage')->group(function () {
            Route::apiResource('sekolah', SekolahController::class);
        });

        Route::middleware('permission:pengguna.manage')->group(function () {
            Route::apiResource('pengguna', PenggunaController::class);
        });

        Route::middleware('permission:sesi-supervisi.manage')->group(function () {
            Route::apiResource('sesi-supervisi', SesiSupervisiController::class)->only(['index', 'store', 'show']);
            Route::patch('/sesi-supervisi/{sesi_supervisi}/pra-observasi', [SesiSupervisiController::class, 'praObservasi'])
                ->name('sesi-supervisi.pra-observasi');
        });
    });
});
