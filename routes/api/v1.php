<?php

use App\Modules\Analisis\Http\Controllers\AnalisisController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Instrumen\Http\Controllers\InstrumenController;
use App\Modules\Observasi\Http\Controllers\ObservasiController;
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
            Route::post('/sesi-supervisi/{sesi_supervisi}/observasi', [ObservasiController::class, 'simpan'])
                ->name('sesi-supervisi.observasi');
            Route::get('/sesi-supervisi/{sesi_supervisi}/skor', [AnalisisController::class, 'skor'])
                ->name('sesi-supervisi.skor');
        });

        Route::middleware('permission:instrumen.manage')->group(function () {
            Route::apiResource('instrumen-observasi', InstrumenController::class)->only(['index', 'store', 'show', 'update']);
            Route::patch('/instrumen-observasi/{instrumen_observasi}/aktifkan', [InstrumenController::class, 'aktifkan'])
                ->name('instrumen-observasi.aktifkan');
            Route::post('/instrumen-observasi/{instrumen_observasi}/butir', [InstrumenController::class, 'tambahButir'])
                ->name('instrumen-observasi.butir.store');
            Route::patch('/butir-observasi/{butir_observasi}', [InstrumenController::class, 'updateButir'])
                ->name('butir-observasi.update');
            Route::delete('/butir-observasi/{butir_observasi}', [InstrumenController::class, 'hapusButir'])
                ->name('butir-observasi.destroy');
        });
    });
});
