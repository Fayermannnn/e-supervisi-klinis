<?php

namespace App\Livewire\Pengguna;

use App\Models\Pengguna as PenggunaModel;
use App\Models\Sekolah;
use App\Modules\Pengguna\Services\PenggunaService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah Pengguna')]
class Create extends Component
{
    public ?string $sekolah_id = null;

    public string $nama = '';

    public string $email = '';

    public string $password = '';

    public ?string $nip_nuptk = null;

    public ?string $no_telepon = null;

    public bool $sekolahTerkunci = false;

    public function mount(): void
    {
        /** @var PenggunaModel $actor */
        $actor = auth()->user();

        if ($actor->hasRole('kepala_sekolah')) {
            $this->sekolah_id = $actor->sekolah_id;
            $this->sekolahTerkunci = true;
        }
    }

    public function simpan(PenggunaService $penggunaService): void
    {
        $data = $this->validate([
            'sekolah_id' => ['nullable', 'uuid', 'exists:sekolah,id'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:8'],
            'nip_nuptk' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authorize('create', [PenggunaModel::class, $data['sekolah_id'] ?? null]);

        $penggunaService->create($data);

        session()->flash('status', 'Pengguna berhasil ditambahkan.');

        $this->redirect(route('app.pengguna.index'));
    }

    public function render()
    {
        return view('livewire.pengguna.create', [
            'daftarSekolah' => Sekolah::query()->orderBy('nama_sekolah')->get(),
        ]);
    }
}
