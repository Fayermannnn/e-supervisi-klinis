<?php

namespace App\Exceptions;

/**
 * BR-04: RTL wajib diisi sebelum sesi ditandai selesai. Ini adalah
 * penegakan langsung terhadap bottleneck B-04 (Umpan Balik -> Tindak
 * Lanjut terputus) yang menjadi prioritas tertinggi perbaikan di SDD
 * (Bagian 3, Analisis Masalah).
 */
class RtlRequiredBeforeCloseException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Rencana Tindak Lanjut wajib diisi sebelum sesi dapat ditandai selesai (BR-04).');
    }

    public function errorCode(): string
    {
        return 'RTL_REQUIRED_BEFORE_CLOSE';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
