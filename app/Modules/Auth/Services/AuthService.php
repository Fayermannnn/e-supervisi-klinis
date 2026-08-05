<?php

namespace App\Modules\Auth\Services;

use App\Exceptions\AuthInvalidCredentialsException;
use App\Exceptions\AuthTooManyAttemptsException;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly LoginRateLimiter $rateLimiter) {}

    /**
     * Verifikasi kredensial dan terbitkan token Sanctum.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{token: string, pengguna: Pengguna}
     *
     * @throws AuthInvalidCredentialsException
     * @throws AuthTooManyAttemptsException
     */
    public function login(array $credentials, string $ip): array
    {
        $key = $this->rateLimiter->key($credentials['email'], $ip);

        if ($this->rateLimiter->tooManyAttempts($key)) {
            throw new AuthTooManyAttemptsException($this->rateLimiter->availableInSeconds($key));
        }

        $pengguna = Pengguna::where('email', $credentials['email'])->first();

        if (! $pengguna || ! Hash::check($credentials['password'], $pengguna->password)) {
            $this->rateLimiter->hit($key);

            throw new AuthInvalidCredentialsException;
        }

        $this->rateLimiter->clear($key);

        $token = $pengguna->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'pengguna' => $pengguna,
        ];
    }
}
