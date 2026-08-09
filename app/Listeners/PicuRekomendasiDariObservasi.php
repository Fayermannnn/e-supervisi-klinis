<?php

namespace App\Listeners;

use App\Events\HasilObservasiDifinalisasi;
use App\Modules\PengembanganProfesional\Services\RekomendasiService;

class PicuRekomendasiDariObservasi
{
    public function __construct(private readonly RekomendasiService $rekomendasiService) {}

    public function handle(HasilObservasiDifinalisasi $event): void
    {
        $this->rekomendasiService->picuDariObservasi($event->sesi);
    }
}
