<?php

namespace App\Livewire\Observasi;

use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use App\Modules\Observasi\Services\HasilObservasiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Form Observasi')]
class FormObservasi extends Component
{
    public SesiSupervisi $sesiSupervisi;

    /** @var array<string, int|null> butir_id => skor */
    public array $skor = [];

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('update', $sesiSupervisi);

        $this->sesiSupervisi = $sesiSupervisi;

        foreach ($this->instrumenAktif()?->butir ?? [] as $butir) {
            $this->skor[$butir->id] = null;
        }

        foreach ($sesiSupervisi->hasilObservasi as $hasil) {
            $this->skor[$hasil->butir_id] = $hasil->skor;
        }
    }

    public function simpanDraft(HasilObservasiService $hasilObservasiService): void
    {
        $this->simpan($hasilObservasiService, statusAkhir: false);

        session()->flash('status', 'Progres observasi disimpan sebagai draft.');
    }

    public function selesai(HasilObservasiService $hasilObservasiService): void
    {
        $this->simpan($hasilObservasiService, statusAkhir: true);

        session()->flash('status', 'Observasi selesai dan sesi berpindah ke tahap Dianalisis.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    private function simpan(HasilObservasiService $hasilObservasiService, bool $statusAkhir): void
    {
        $butir = collect($this->skor)
            ->filter(fn ($nilai) => $nilai !== null)
            ->map(fn ($nilai, $butirId) => ['butir_id' => $butirId, 'skor' => (int) $nilai])
            ->values()
            ->all();

        $this->sesiSupervisi = $hasilObservasiService->simpanHasil($this->sesiSupervisi->id, [
            'butir' => $butir,
            'status_akhir' => $statusAkhir,
        ]);
    }

    private function instrumenAktif(): ?InstrumenObservasi
    {
        return $this->sesiSupervisi->instrumen
            ?? InstrumenObservasi::where('terkunci', true)->latest('versi')->first();
    }

    public function render()
    {
        $instrumen = $this->instrumenAktif();

        return view('livewire.observasi.form-observasi', [
            'butirPerDimensi' => $instrumen?->butir->groupBy('dimensi') ?? collect(),
            'jumlahTerisi' => collect($this->skor)->filter(fn ($nilai) => $nilai !== null)->count(),
            'jumlahButir' => count($this->skor),
        ]);
    }
}
