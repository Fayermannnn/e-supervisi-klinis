<?php

namespace App\Livewire\Auth;

use App\Models\Pengguna;
use App\Modules\Auth\Services\LoginRateLimiter;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.centered')]
#[Title('Masuk')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public string $twoFactorCode = '';

    public bool $butuhTwoFactor = false;

    public function login(LoginRateLimiter $rateLimiter, TwoFactorService $twoFactorService): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = $rateLimiter->key($this->email, request()->ip());

        if ($rateLimiter->tooManyAttempts($key)) {
            $menit = (int) ceil($rateLimiter->availableInSeconds($key) / 60);
            $this->addError('form', "Terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit.");

            return;
        }

        $pengguna = Pengguna::where('email', $this->email)->first();

        if (! $pengguna || ! Hash::check($this->password, $pengguna->password)) {
            $rateLimiter->hit($key);
            $this->addError('form', 'Email atau kata sandi salah.');

            return;
        }

        if ($twoFactorService->wajib2fa($pengguna) && $twoFactorService->sudahAktif($pengguna)) {
            if (! $this->butuhTwoFactor) {
                $this->butuhTwoFactor = true;

                return;
            }

            $this->validate(['twoFactorCode' => ['required', 'string']]);

            if (! $twoFactorService->verifikasiLogin($pengguna, $this->twoFactorCode)) {
                $rateLimiter->hit($key);
                $this->addError('twoFactorCode', 'Kode verifikasi salah.');

                return;
            }
        }

        $rateLimiter->clear($key);

        Auth::login($pengguna);

        session()->regenerate();

        $this->redirect(route('beranda'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
