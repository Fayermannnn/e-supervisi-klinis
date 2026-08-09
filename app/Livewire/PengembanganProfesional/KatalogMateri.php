<?php

namespace App\Livewire\PengembanganProfesional;

use App\Models\MateriPengembangan;
use App\Modules\PengembanganProfesional\Services\MateriService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Katalog Materi Pengembangan')]
class KatalogMateri extends Component
{
    use WithPagination;

    public bool $formTerbuka = false;

    public ?string $editId = null;

    public string $judul = '';

    public string $kategori = 'pedagogik';

    public ?string $tautan_atau_deskripsi = null;

    public function mount(): void
    {
        $this->authorize('viewAny', MateriPengembangan::class);
    }

    public function bukaForm(): void
    {
        $this->authorize('create', MateriPengembangan::class);
        $this->reset(['editId', 'judul', 'kategori', 'tautan_atau_deskripsi']);
        $this->kategori = 'pedagogik';
        $this->formTerbuka = true;
    }

    public function edit(string $id, MateriService $materiService): void
    {
        $this->authorize('update', MateriPengembangan::class);
        $materi = $materiService->find($id);

        $this->editId = $materi->id;
        $this->judul = $materi->judul;
        $this->kategori = $materi->kategori;
        $this->tautan_atau_deskripsi = $materi->tautan_atau_deskripsi;
        $this->formTerbuka = true;
    }

    public function simpan(MateriService $materiService): void
    {
        $data = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:pedagogik,kepribadian,sosial,profesional'],
            'tautan_atau_deskripsi' => ['nullable', 'string'],
        ]);

        if ($this->editId) {
            $this->authorize('update', MateriPengembangan::class);
            $materiService->update($this->editId, $data);
        } else {
            $this->authorize('create', MateriPengembangan::class);
            $materiService->create($data);
        }

        $this->formTerbuka = false;
        session()->flash('status', 'Materi pengembangan berhasil disimpan.');
    }

    public function hapus(string $id, MateriService $materiService): void
    {
        $this->authorize('delete', MateriPengembangan::class);
        $materiService->delete($id);
        session()->flash('status', 'Materi pengembangan berhasil dihapus.');
    }

    public function render(MateriService $materiService)
    {
        return view('livewire.pengembanganprofesional.katalog-materi', [
            'materi' => $materiService->list(),
        ]);
    }
}
