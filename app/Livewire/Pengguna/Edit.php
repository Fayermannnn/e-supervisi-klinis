<?php

namespace App\Livewire\Pengguna;

use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Modules\Pengguna\Services\PenggunaService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ubah Pengguna')]
class Edit extends Component
{
    public Pengguna $pengguna;

    public ?string $sekolah_id = null;

    public string $nama = '';

    public string $email = '';

    public string $password = '';

    public ?string $nip_nuptk = null;

    public ?string $no_telepon = null;

    public bool $status_aktif = true;

    public bool $sekolahTerkunci = false;

    public function mount(Pengguna $pengguna): void
    {
        $this->authorize('update', $pengguna);

        $this->pengguna = $pengguna;
        $this->sekolah_id = $pengguna->sekolah_id;
        $this->nama = $pengguna->nama;
        $this->email = $pengguna->email;
        $this->nip_nuptk = $pengguna->nip_nuptk;
        $this->no_telepon = $pengguna->no_telepon;
        $this->status_aktif = $pengguna->status_aktif;

        $this->sekolahTerkunci = auth()->user()->hasRole('kepala_sekolah');
    }

    public function simpan(PenggunaService $penggunaService): void
    {
        $data = $this->validate([
            'sekolah_id' => ['nullable', 'uuid', 'exists:sekolah,id'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('pengguna', 'email')->ignore($this->pengguna->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'nip_nuptk' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:255'],
            'status_aktif' => ['boolean'],
        ]);

        if ($this->sekolahTerkunci) {
            $data['sekolah_id'] = $this->pengguna->sekolah_id;
        }

        $penggunaService->update($this->pengguna->id, $data);

        session()->flash('status', 'Pengguna berhasil diperbarui.');

        $this->redirect(route('app.pengguna.index'));
    }

    public function render()
    {
        return view('livewire.pengguna.edit', [
            'daftarSekolah' => Sekolah::query()->orderBy('nama_sekolah')->get(),
        ]);
    }
}
