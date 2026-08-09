<?php

namespace App\Exceptions;

class HasilObservasiBelumLengkapException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Seluruh butir observasi wajib diisi sebelum sesi ditandai selesai dianalisis.');
    }

    public function errorCode(): string
    {
        return 'HASIL_OBSERVASI_BELUM_LENGKAP';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
