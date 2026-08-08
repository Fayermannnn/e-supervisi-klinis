<?php

namespace App\Livewire\Sekolah;

use App\Models\Sekolah;
use App\Modules\Sekolah\Services\SekolahService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Sekolah')]
class Create extends Component
{
    public string $nama_sekolah = '';

    public ?string $npsn = null;

    public ?string $alamat = null;

    public function mount(): void
    {
        $this->authorize('create', Sekolah::class);
    }

    public function simpan(SekolahService $sekolahService): void
    {
        $data = $this->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20', 'unique:sekolah,npsn'],
            'alamat' => ['nullable', 'string'],
        ]);

        $sekolahService->create($data);

        session()->flash('status', 'Sekolah berhasil ditambahkan.');

        $this->redirect(route('app.sekolah.index'));
    }

    public function render()
    {
        return view('livewire.sekolah.create');
    }
}
