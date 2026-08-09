<?php

namespace App\Events;

use App\Models\RencanaTindakLanjut;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * BR-09a (Addendum 02): rekomendasi materi PD otomatis dipicu kecocokan
 * kategori RTL <-> katalog materi, saat RTL disimpan.
 */
class RtlDiisi
{
    use Dispatchable;

    public function __construct(public readonly RencanaTindakLanjut $rtl) {}
}
