<?php

namespace App\Events;

use App\Models\SesiSupervisi;
use Illuminate\Foundation\Events\Dispatchable;

class SesiSupervisiDijadwalkan
{
    use Dispatchable;

    public function __construct(public readonly SesiSupervisi $sesiSupervisi) {}
}
