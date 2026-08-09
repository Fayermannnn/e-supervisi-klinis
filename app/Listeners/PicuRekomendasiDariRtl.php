<?php

namespace App\Listeners;

use App\Events\RtlDiisi;
use App\Modules\PengembanganProfesional\Services\RekomendasiService;

class PicuRekomendasiDariRtl
{
    public function __construct(private readonly RekomendasiService $rekomendasiService) {}

    public function handle(RtlDiisi $event): void
    {
        $this->rekomendasiService->picuDariRtl($event->rtl);
    }
}
