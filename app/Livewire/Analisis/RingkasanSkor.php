<?php

namespace App\Livewire\Analisis;

use App\Models\SesiSupervisi;
use App\Modules\Analisis\Services\SkorCalculator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ringkasan Skor')]
class RingkasanSkor extends Component
{
    public SesiSupervisi $sesiSupervisi;

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('view', $sesiSupervisi);

        $this->sesiSupervisi = $sesiSupervisi;
    }

    public function render(SkorCalculator $skorCalculator)
    {
        return view('livewire.analisis.ringkasan-skor', $skorCalculator->hitung($this->sesiSupervisi));
    }
}
