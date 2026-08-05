<?php

namespace App\Modules\Auth\Services;

use App\Exceptions\AuthInvalidCredentialsException;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Verifikasi kredensial dan terbitkan token Sanctum.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{token: string, pengguna: Pengguna}
     *
     * @throws AuthInvalidCredentialsException
     */
    public function login(array $credentials): array
    {
        $pengguna = Pengguna::where('email', $credentials['email'])->first();

        if (! $pengguna || ! Hash::check($credentials['password'], $pengguna->password)) {
            throw new AuthInvalidCredentialsException;
        }

        $token = $pengguna->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'pengguna' => $pengguna,
        ];
    }
}
