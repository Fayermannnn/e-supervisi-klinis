<?php

namespace App\Modules\Auth\Services;

use App\Exceptions\AuthInvalidCredentialsException;
use App\Exceptions\AuthInvalidTwoFactorCodeException;
use App\Exceptions\AuthTooManyAttemptsException;
use App\Exceptions\AuthTwoFactorRequiredException;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly LoginRateLimiter $rateLimiter,
        private readonly TwoFactorService $twoFactorService,
    ) {}

    /**
     * Verifikasi kredensial dan terbitkan token Sanctum.
     *
     * @param  array{email: string, password: string, two_factor_code?: string|null}  $credentials
     * @return array{token: string, pengguna: Pengguna}
     *
     * @throws AuthInvalidCredentialsException
     * @throws AuthTooManyAttemptsException
     * @throws AuthTwoFactorRequiredException
     * @throws AuthInvalidTwoFactorCodeException
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

        if ($this->twoFactorService->wajib2fa($pengguna) && $this->twoFactorService->sudahAktif($pengguna)) {
            $kode = $credentials['two_factor_code'] ?? null;

            if (! $kode) {
                throw new AuthTwoFactorRequiredException;
            }

            if (! $this->twoFactorService->verifikasiLogin($pengguna, $kode)) {
                $this->rateLimiter->hit($key);

                throw new AuthInvalidTwoFactorCodeException;
            }
        }

        $this->rateLimiter->clear($key);

        $token = $pengguna->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'pengguna' => $pengguna,
        ];
    }
}
