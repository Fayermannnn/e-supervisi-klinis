<?php

namespace App\Exceptions;

class RtlNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Rencana Tindak Lanjut untuk sesi ini belum tersedia.');
    }

    public function errorCode(): string
    {
        return 'RTL_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
