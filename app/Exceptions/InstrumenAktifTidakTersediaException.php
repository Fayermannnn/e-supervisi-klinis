<?php

namespace App\Exceptions;

class InstrumenAktifTidakTersediaException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Tidak ada instrumen observasi aktif (terkunci) yang bisa dipakai untuk observasi ini.');
    }

    public function errorCode(): string
    {
        return 'INSTRUMEN_AKTIF_TIDAK_TERSEDIA';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
