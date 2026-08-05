<?php

namespace App\Exceptions;

class PenggunaNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Pengguna tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'PENGGUNA_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
