<?php

namespace App\Livewire\UmpanBalik;

use App\Models\SesiSupervisi;
use App\Modules\UmpanBalik\Services\UmpanBalikService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Refleksi Guru')]
class RefleksiGuru extends Component
{
    public SesiSupervisi $sesiSupervisi;

    public string $kekuatan = '';

    public string $area_pengembangan = '';

    public string $rekomendasi = '';

    public string $refleksi_guru = '';

    public function mount(SesiSupervisi $sesiSupervisi, UmpanBalikService $umpanBalikService): void
    {
        $umpanBalik = $umpanBalikService->find($sesiSupervisi->id);

        $this->authorize('refleksi', $umpanBalik);

        $this->sesiSupervisi = $sesiSupervisi;
        $this->kekuatan = $umpanBalik->kekuatan;
        $this->area_pengembangan = $umpanBalik->area_pengembangan;
        $this->rekomendasi = $umpanBalik->rekomendasi;
        $this->refleksi_guru = (string) $umpanBalik->refleksi_guru;
    }

    public function simpan(UmpanBalikService $umpanBalikService): void
    {
        $data = $this->validate([
            'refleksi_guru' => ['required', 'string'],
        ]);

        $umpanBalikService->isiRefleksi($this->sesiSupervisi->id, $data['refleksi_guru']);

        session()->flash('status', 'Refleksi Anda berhasil disimpan.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    public function render()
    {
        return view('livewire.umpanbalik.refleksi-guru');
    }
}
