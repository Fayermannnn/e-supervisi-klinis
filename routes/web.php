<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\SetupTwoFactor;
use App\Livewire\Instrumen\Create as InstrumenCreate;
use App\Livewire\Instrumen\Index as InstrumenIndex;
use App\Livewire\Instrumen\Kelola as InstrumenKelola;
use App\Livewire\Observasi\FormObservasi;
use App\Livewire\Pengguna\Create as PenggunaCreate;
use App\Livewire\Pengguna\Edit as PenggunaEdit;
use App\Livewire\Pengguna\Index as PenggunaIndex;
use App\Livewire\Perencanaan\BuatJadwal;
use App\Livewire\Perencanaan\Index as SesiSupervisiIndex;
use App\Livewire\Perencanaan\PraObservasi;
use App\Livewire\Sekolah\Create as SekolahCreate;
use App\Livewire\Sekolah\Edit as SekolahEdit;
use App\Livewire\Sekolah\Index as SekolahIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', Login::class)->middleware('guest')->name('login');

Route::get('/beranda', function () {
    return view('beranda');
})->middleware('auth')->name('beranda');

Route::get('/2fa/aktivasi', SetupTwoFactor::class)->middleware('auth')->name('two-factor.setup');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'permission:sekolah.manage'])->name('app.')->group(function () {
    Route::get('/sekolah', SekolahIndex::class)->name('sekolah.index');
    Route::get('/sekolah/tambah', SekolahCreate::class)->name('sekolah.create');
    Route::get('/sekolah/{sekolah}/ubah', SekolahEdit::class)->name('sekolah.edit');
});

Route::middleware(['auth', 'permission:pengguna.manage'])->name('app.')->group(function () {
    Route::get('/pengguna', PenggunaIndex::class)->name('pengguna.index');
    Route::get('/pengguna/tambah', PenggunaCreate::class)->name('pengguna.create');
    Route::get('/pengguna/{pengguna}/ubah', PenggunaEdit::class)->name('pengguna.edit');
});

Route::middleware(['auth', 'permission:sesi-supervisi.manage'])->name('app.')->group(function () {
    Route::get('/sesi-supervisi', SesiSupervisiIndex::class)->name('sesi-supervisi.index');
    Route::get('/sesi-supervisi/buat-jadwal', BuatJadwal::class)->name('sesi-supervisi.create');
    Route::get('/sesi-supervisi/{sesiSupervisi}/pra-observasi', PraObservasi::class)->name('sesi-supervisi.pra-observasi');
    Route::get('/sesi-supervisi/{sesiSupervisi}/observasi', FormObservasi::class)->name('sesi-supervisi.observasi');
});

Route::middleware(['auth', 'permission:instrumen.manage'])->name('app.')->group(function () {
    Route::get('/instrumen', InstrumenIndex::class)->name('instrumen.index');
    Route::get('/instrumen/buat', InstrumenCreate::class)->name('instrumen.create');
    Route::get('/instrumen/{instrumen}/kelola', InstrumenKelola::class)->name('instrumen.kelola');
});
