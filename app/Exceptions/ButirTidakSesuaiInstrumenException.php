<?php

namespace App\Exceptions;

class ButirTidakSesuaiInstrumenException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Butir yang dikirim tidak sesuai dengan instrumen yang dipakai sesi ini.');
    }

    public function errorCode(): string
    {
        return 'BUTIR_TIDAK_SESUAI_INSTRUMEN';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
