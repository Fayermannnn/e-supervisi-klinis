<?php

namespace App\Exceptions;

class UmpanBalikNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Umpan balik untuk sesi ini belum tersedia.');
    }

    public function errorCode(): string
    {
        return 'UMPAN_BALIK_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
