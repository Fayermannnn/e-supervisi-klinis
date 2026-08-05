<?php

namespace App\Exceptions;

class SekolahNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Sekolah tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'SEKOLAH_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
