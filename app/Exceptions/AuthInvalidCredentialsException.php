<?php

namespace App\Exceptions;

class AuthInvalidCredentialsException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Email atau kata sandi salah.');
    }

    public function errorCode(): string
    {
        return 'AUTH_INVALID_CREDENTIALS';
    }

    public function statusCode(): int
    {
        return 401;
    }
}
