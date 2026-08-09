<?php

namespace App\Livewire\Perencanaan;

use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Buat Jadwal Sesi Supervisi')]
class BuatJadwal extends Component
{
    public string $guru_id = '';

    public string $tipe_supervisor = 'internal';

    public string $tanggal = '';

    public function mount(): void
    {
        $this->authorize('create', SesiSupervisi::class);
    }

    public function simpan(SesiSupervisiService $sesiSupervisiService): void
    {
        $data = $this->validate([
            'guru_id' => ['required', 'uuid', 'exists:pengguna,id'],
            'tipe_supervisor' => ['required', 'string', 'in:internal,eksternal'],
            'tanggal' => ['required', 'date'],
        ]);

        $data['supervisor_id'] = auth()->id();

        $sesiSupervisiService->buatJadwal($data);

        session()->flash('status', 'Jadwal sesi supervisi berhasil dibuat.');

        $this->redirect(route('app.sesi-supervisi.index'));
    }

    public function render()
    {
        return view('livewire.perencanaan.buat-jadwal', [
            'daftarGuru' => Pengguna::role('guru')->orderBy('nama')->get(),
        ]);
    }
}
