<?php

namespace App\Exceptions;

class InstrumenObservasiNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Instrumen observasi tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'INSTRUMEN_OBSERVASI_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
