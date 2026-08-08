<?php

namespace App\Livewire\Sekolah;

use App\Models\Sekolah;
use App\Modules\Sekolah\Services\SekolahService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ubah Sekolah')]
class Edit extends Component
{
    public Sekolah $sekolah;

    public string $nama_sekolah = '';

    public ?string $npsn = null;

    public ?string $alamat = null;

    public bool $status_aktif = true;

    public function mount(Sekolah $sekolah): void
    {
        $this->sekolah = $sekolah;
        $this->nama_sekolah = $sekolah->nama_sekolah;
        $this->npsn = $sekolah->npsn;
        $this->alamat = $sekolah->alamat;
        $this->status_aktif = $sekolah->status_aktif;
    }

    public function simpan(SekolahService $sekolahService): void
    {
        $data = $this->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20', Rule::unique('sekolah', 'npsn')->ignore($this->sekolah->id)],
            'alamat' => ['nullable', 'string'],
            'status_aktif' => ['boolean'],
        ]);

        $sekolahService->update($this->sekolah->id, $data);

        session()->flash('status', 'Sekolah berhasil diperbarui.');

        $this->redirect(route('app.sekolah.index'));
    }

    public function render()
    {
        return view('livewire.sekolah.edit');
    }
}
