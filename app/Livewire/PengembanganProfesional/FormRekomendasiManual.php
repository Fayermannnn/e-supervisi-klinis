<?php

namespace App\Livewire\PengembanganProfesional;

use App\Models\MateriPengembangan;
use App\Models\RekomendasiPengembangan;
use App\Models\SesiSupervisi;
use App\Modules\PengembanganProfesional\Services\RekomendasiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Rekomendasikan Materi')]
class FormRekomendasiManual extends Component
{
    public SesiSupervisi $sesiSupervisi;

    public string $materi_id = '';

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('create', [RekomendasiPengembangan::class, $sesiSupervisi]);

        $this->sesiSupervisi = $sesiSupervisi;
    }

    public function simpan(RekomendasiService $rekomendasiService): void
    {
        $data = $this->validate([
            'materi_id' => ['required', 'uuid', 'exists:materi_pengembangan,id'],
        ]);

        $rekomendasiService->rekomendasikanManual([
            'pengguna_id' => $this->sesiSupervisi->guru_id,
            'sesi_id' => $this->sesiSupervisi->id,
            'materi_id' => $data['materi_id'],
        ]);

        session()->flash('status', 'Rekomendasi materi berhasil dikirim ke guru.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    public function render()
    {
        return view('livewire.pengembanganprofesional.form-rekomendasi-manual', [
            'daftarMateri' => MateriPengembangan::orderBy('judul')->get(),
        ]);
    }
}
