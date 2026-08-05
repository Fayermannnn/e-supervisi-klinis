<?php

namespace App\Livewire\Auth;

use App\Modules\Auth\Services\LoginRateLimiter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.centered')]
#[Title('Masuk')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function login(LoginRateLimiter $rateLimiter): void
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

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $rateLimiter->hit($key);
            $this->addError('form', 'Email atau kata sandi salah.');

            return;
        }

        $rateLimiter->clear($key);

        session()->regenerate();

        $this->redirect(route('beranda'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
