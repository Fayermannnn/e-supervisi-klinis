<?php

use App\Livewire\Auth\Login;
use App\Livewire\Pengguna\Create as PenggunaCreate;
use App\Livewire\Pengguna\Edit as PenggunaEdit;
use App\Livewire\Pengguna\Index as PenggunaIndex;
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

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->name('app.')->group(function () {
    Route::get('/sekolah', SekolahIndex::class)->name('sekolah.index');
    Route::get('/sekolah/tambah', SekolahCreate::class)->name('sekolah.create');
    Route::get('/sekolah/{sekolah}/ubah', SekolahEdit::class)->name('sekolah.edit');

    Route::get('/pengguna', PenggunaIndex::class)->name('pengguna.index');
    Route::get('/pengguna/tambah', PenggunaCreate::class)->name('pengguna.create');
    Route::get('/pengguna/{pengguna}/ubah', PenggunaEdit::class)->name('pengguna.edit');
});
