<?php

namespace App\Exceptions;

class Br01FokusObservasiKosongException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Pra-observasi wajib diisi dengan fokus observasi sebelum sesi dapat dilanjutkan.');
    }

    public function errorCode(): string
    {
        return 'BR01_FOKUS_OBSERVASI_KOSONG';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
