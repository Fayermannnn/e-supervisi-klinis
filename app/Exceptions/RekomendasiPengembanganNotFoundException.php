<?php

namespace App\Exceptions;

class RekomendasiPengembanganNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Rekomendasi pengembangan tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'REKOMENDASI_PENGEMBANGAN_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
