<?php

namespace App\Events;

use App\Models\SesiSupervisi;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * BR-09b (Addendum 02, Dual Trigger): rekomendasi materi PD otomatis JUGA
 * dipicu saat skor butir observasi <=2 disimpan sebagai hasil final
 * (status_akhir:true), mengacu pemetaan butir->materi pada instrumen
 * aktif. Independen dari BR-09a, tanpa deduplikasi otomatis (Addendum 02).
 */
class HasilObservasiDifinalisasi
{
    use Dispatchable;

    public function __construct(public readonly SesiSupervisi $sesi) {}
}
