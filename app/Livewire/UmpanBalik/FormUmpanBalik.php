<?php

namespace App\Livewire\UmpanBalik;

use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use App\Modules\UmpanBalik\Services\UmpanBalikService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Form Umpan Balik')]
class FormUmpanBalik extends Component
{
    /**
     * Developmental Supervision (Sprint 7 AC: "Satu form, penekanan
     * berubah otomatis") - satu set field yang sama, label & bantuan
     * menyesuaikan pendekatan_dipakai. SDD tidak menetapkan teks label
     * persis; framing di bawah mengikuti kontinum Glickman (directive
     * control -> nondirective, dari instruktif ke reflektif).
     */
    private const FRAMING = [
        'directive_control' => [
            'kekuatan' => 'Hal yang Sudah Dilakukan dengan Benar',
            'area_pengembangan' => 'Yang Wajib Diperbaiki',
            'rekomendasi' => 'Instruksi Langkah Konkret',
        ],
        'directive_informational' => [
            'kekuatan' => 'Kekuatan yang Teramati',
            'area_pengembangan' => 'Area yang Perlu Diperbaiki',
            'rekomendasi' => 'Pilihan Strategi yang Disarankan',
        ],
        'collaborative' => [
            'kekuatan' => 'Kekuatan yang Kita Diskusikan Bersama',
            'area_pengembangan' => 'Area yang Bisa Dikembangkan Bersama',
            'rekomendasi' => 'Kesepakatan Langkah Berikutnya',
        ],
        'nondirective' => [
            'kekuatan' => 'Menurut Anda, Apa yang Sudah Berjalan Baik?',
            'area_pengembangan' => 'Apa yang Ingin Anda Kembangkan?',
            'rekomendasi' => 'Ide Anda untuk Langkah Selanjutnya',
        ],
    ];

    public SesiSupervisi $sesiSupervisi;

    public string $kekuatan = '';

    public string $area_pengembangan = '';

    public string $rekomendasi = '';

    public ?string $pendekatan_dipakai = null;

    public function mount(SesiSupervisi $sesiSupervisi): void
    {
        $this->authorize('create', [UmpanBalik::class, $sesiSupervisi]);

        $this->sesiSupervisi = $sesiSupervisi;
        $this->pendekatan_dipakai = $sesiSupervisi->pendekatan_disarankan;
    }

    public function getLabelProperty(): array
    {
        return self::FRAMING[$this->pendekatan_dipakai] ?? [
            'kekuatan' => 'Kekuatan',
            'area_pengembangan' => 'Area Pengembangan',
            'rekomendasi' => 'Rekomendasi',
        ];
    }

    public function simpan(UmpanBalikService $umpanBalikService): void
    {
        $data = $this->validate([
            'kekuatan' => ['required', 'string'],
            'area_pengembangan' => ['required', 'string'],
            'rekomendasi' => ['required', 'string'],
            'pendekatan_dipakai' => ['nullable', 'string', 'in:directive_control,directive_informational,collaborative,nondirective'],
        ]);

        $umpanBalikService->isiUmpanBalik($this->sesiSupervisi->id, $data);

        session()->flash('status', 'Umpan balik berhasil disimpan.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    public function render()
    {
        return view('livewire.umpanbalik.form-umpan-balik');
    }
}
