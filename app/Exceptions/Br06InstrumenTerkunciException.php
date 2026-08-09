<?php

namespace App\Exceptions;

class Br06InstrumenTerkunciException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Instrumen versi aktif terkunci dan tidak bisa diedit.');
    }

    public function errorCode(): string
    {
        return 'BR06_INSTRUMEN_TERKUNCI';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
