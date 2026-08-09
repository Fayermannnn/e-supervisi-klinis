<?php

namespace App\Exceptions;

class ButirObservasiNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Butir observasi tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'BUTIR_OBSERVASI_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
