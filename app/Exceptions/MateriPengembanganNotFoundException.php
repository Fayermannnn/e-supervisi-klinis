<?php

namespace App\Exceptions;

class MateriPengembanganNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Materi pengembangan tidak ditemukan.');
    }

    public function errorCode(): string
    {
        return 'MATERI_PENGEMBANGAN_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
