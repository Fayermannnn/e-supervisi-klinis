<?php

namespace App\Livewire\Perencanaan;

use App\Models\SesiSupervisi;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pra-Observasi')]
class PraObservasi extends Component
{
    public SesiSupervisi $sesiSupervisi;

    public string $fokus_observasi = '';

    public ?string $level_perkembangan_guru = null;

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('update', $sesiSupervisi);

        $this->sesiSupervisi = $sesiSupervisi;
        $this->fokus_observasi = (string) $sesiSupervisi->fokus_observasi;
        $this->level_perkembangan_guru = $sesiSupervisi->level_perkembangan_guru;
    }

    public function simpan(SesiSupervisiService $sesiSupervisiService): void
    {
        $data = $this->validate([
            'fokus_observasi' => ['required', 'string'],
            'level_perkembangan_guru' => ['nullable', 'string', 'in:rendah,sedang_rendah,sedang_tinggi,tinggi'],
        ]);

        $sesiSupervisiService->isiPraObservasi($this->sesiSupervisi->id, $data);

        session()->flash('status', 'Pra-observasi berhasil disimpan.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    public function render()
    {
        return view('livewire.perencanaan.pra-observasi');
    }
}
