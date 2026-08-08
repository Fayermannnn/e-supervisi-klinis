<?php

namespace App\Exceptions;

class AuthTwoFactorRequiredException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Kode verifikasi dua langkah diperlukan.');
    }

    public function errorCode(): string
    {
        return 'AUTH_TWO_FACTOR_REQUIRED';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
