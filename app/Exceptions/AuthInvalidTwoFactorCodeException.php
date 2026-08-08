<?php

namespace App\Exceptions;

class AuthInvalidTwoFactorCodeException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Kode verifikasi dua langkah salah.');
    }

    public function errorCode(): string
    {
        return 'AUTH_INVALID_TWO_FACTOR_CODE';
    }

    public function statusCode(): int
    {
        return 401;
    }
}
