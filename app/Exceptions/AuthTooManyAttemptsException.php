<?php

namespace App\Exceptions;

class AuthTooManyAttemptsException extends ApiException
{
    public function __construct(int $availableInSeconds)
    {
        $menit = (int) ceil($availableInSeconds / 60);

        parent::__construct("Terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit.");
    }

    public function errorCode(): string
    {
        return 'AUTH_TOO_MANY_ATTEMPTS';
    }

    public function statusCode(): int
    {
        return 429;
    }
}
