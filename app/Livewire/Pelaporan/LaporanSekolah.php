<?php

namespace App\Livewire\Pelaporan;

use App\Models\Sekolah;
use App\Modules\Pelaporan\Queries\LaporanAgregatQuery;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Sekolah')]
class LaporanSekolah extends Component
{
    public Sekolah $sekolah;

    public function mount(Sekolah $sekolah): void
    {
        $this->authorize('viewLaporan', $sekolah);

        $this->sekolah = $sekolah;
    }

    public function render(LaporanAgregatQuery $laporanAgregatQuery)
    {
        return view('livewire.pelaporan.laporan-sekolah', $laporanAgregatQuery->rekapSekolah($this->sekolah));
    }
}
