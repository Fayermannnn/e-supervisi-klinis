<?php

namespace App\Livewire\TindakLanjut;

use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use App\Modules\TindakLanjut\Services\RtlService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Rencana Tindak Lanjut')]
class FormRtl extends Component
{
    public SesiSupervisi $sesiSupervisi;

    public string $deskripsi = '';

    public string $target_waktu = '';

    public ?string $kategori = null;

    public bool $sudahAda = false;

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('create', [RencanaTindakLanjut::class, $sesiSupervisi]);

        $this->sesiSupervisi = $sesiSupervisi;

        $rtl = $sesiSupervisi->rtl;

        if ($rtl) {
            $this->sudahAda = true;
            $this->deskripsi = $rtl->deskripsi;
            $this->target_waktu = $rtl->target_waktu->toDateString();
            $this->kategori = $rtl->kategori;
        }
    }

    public function simpan(RtlService $rtlService): void
    {
        $data = $this->validate([
            'deskripsi' => ['required', 'string'],
            'target_waktu' => ['required', 'date'],
            'kategori' => ['nullable', 'string', 'in:pedagogik,kepribadian,sosial,profesional'],
        ]);

        $rtlService->isiRtl($this->sesiSupervisi->id, $data);

        $this->sudahAda = true;

        session()->flash('status', 'Rencana Tindak Lanjut berhasil disimpan.');
    }

    public function selesaikan(RtlService $rtlService): void
    {
        $rtlService->tandaiSelesai($this->sesiSupervisi->id);

        session()->flash('status', 'Sesi supervisi ditandai selesai.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    public function render()
    {
        return view('livewire.tindaklanjut.form-rtl');
    }
}
