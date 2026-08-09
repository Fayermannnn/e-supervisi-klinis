<?php

namespace App\Livewire\Pelaporan;

use App\Models\SesiSupervisi;
use App\Modules\Pelaporan\Services\PelaporanService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Sprint 11 (Dusk AC: "drill-down Admin Dinas berjustifikasi"): UI minimal
 * untuk endpoint BR-08 (POST /laporan/individual/{sesi_id}, Sprint 9) yang
 * sebelumnya hanya punya endpoint API tanpa layar - Sprint 9 backlog hanya
 * meminta Endpoint, layar ini melengkapinya supaya alur bisa diuji E2E.
 */
#[Layout('layouts.app')]
#[Title('Detail Individual')]
class DrillDownIndividual extends Component
{
    public SesiSupervisi $sesiSupervisi;

    public string $justifikasi = '';

    /** @var array<string, mixed>|null */
    public ?array $hasil = null;

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('laporan.drillDownIndividual');

        $this->sesiSupervisi = $sesiSupervisi;
    }

    public function lihat(PelaporanService $pelaporanService): void
    {
        $this->authorize('laporan.drillDownIndividual');

        $this->validate(['justifikasi' => ['required', 'string', 'min:10']]);

        $this->hasil = $pelaporanService->drillDownIndividual(
            admin: auth()->user(),
            sesiId: $this->sesiSupervisi->id,
            justifikasi: $this->justifikasi,
            ipAddress: request()->ip(),
            correlationId: request()->attributes->get('correlation_id'),
        );
    }

    public function render()
    {
        return view('livewire.pelaporan.drill-down-individual');
    }
}
