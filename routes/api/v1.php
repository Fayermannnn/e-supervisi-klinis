<?php

use App\Modules\Analisis\Http\Controllers\AnalisisController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Instrumen\Http\Controllers\InstrumenController;
use App\Modules\Observasi\Http\Controllers\ObservasiController;
use App\Modules\Pengguna\Http\Controllers\PenggunaController;
use App\Modules\Perencanaan\Http\Controllers\SesiSupervisiController;
use App\Modules\Sekolah\Http\Controllers\SekolahController;
use App\Modules\TindakLanjut\Http\Controllers\RtlController;
use App\Modules\UmpanBalik\Http\Controllers\UmpanBalikController;
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
            Route::post('/sesi-supervisi/{sesi_supervisi}/umpan-balik', [UmpanBalikController::class, 'store'])
                ->name('sesi-supervisi.umpan-balik.store');
            Route::get('/sesi-supervisi/{sesi_supervisi}/umpan-balik', [UmpanBalikController::class, 'show'])
                ->name('sesi-supervisi.umpan-balik.show');
            Route::patch('/sesi-supervisi/{sesi_supervisi}/umpan-balik/pendekatan', [UmpanBalikController::class, 'ubahPendekatan'])
                ->name('sesi-supervisi.umpan-balik.pendekatan');
            Route::patch('/sesi-supervisi/{sesi_supervisi}/umpan-balik/refleksi', [UmpanBalikController::class, 'isiRefleksi'])
                ->name('sesi-supervisi.umpan-balik.refleksi');
            Route::post('/sesi-supervisi/{sesi_supervisi}/rtl', [RtlController::class, 'store'])
                ->name('sesi-supervisi.rtl.store');
            Route::patch('/sesi-supervisi/{sesi_supervisi}/rtl', [RtlController::class, 'update'])
                ->name('sesi-supervisi.rtl.update');
            Route::get('/sesi-supervisi/{sesi_supervisi}/rtl', [RtlController::class, 'show'])
                ->name('sesi-supervisi.rtl.show');
            Route::post('/sesi-supervisi/{sesi_supervisi}/selesaikan', [RtlController::class, 'selesaikan'])
                ->name('sesi-supervisi.selesaikan');
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
