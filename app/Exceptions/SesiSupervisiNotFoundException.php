<?php

namespace App\Exceptions;

class SesiSupervisiNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Sesi supervisi tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'SESI_SUPERVISI_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
