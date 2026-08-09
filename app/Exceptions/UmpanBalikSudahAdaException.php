<?php

namespace App\Exceptions;

class UmpanBalikSudahAdaException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Umpan balik untuk sesi ini sudah pernah diisi.');
    }

    public function errorCode(): string
    {
        return 'UMPAN_BALIK_SUDAH_ADA';
    }

    public function statusCode(): int
    {
        return 409;
    }
}
