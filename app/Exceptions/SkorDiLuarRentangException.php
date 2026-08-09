<?php

namespace App\Exceptions;

class SkorDiLuarRentangException extends ApiException
{
    public function __construct(int $skorMin, int $skorMaks)
    {
        parent::__construct("Skor harus berada di antara {$skorMin} dan {$skorMaks} sesuai instrumen aktif.");
    }

    public function errorCode(): string
    {
        return 'SKOR_DI_LUAR_RENTANG';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
