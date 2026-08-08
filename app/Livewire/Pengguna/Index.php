<?php

namespace App\Livewire\Pengguna;

use App\Modules\Pengguna\Services\PenggunaService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pengguna')]
class Index extends Component
{
    use WithPagination;

    public function hapus(string $id, PenggunaService $penggunaService): void
    {
        $penggunaService->delete($id);

        session()->flash('status', 'Pengguna berhasil dihapus.');
    }

    public function render(PenggunaService $penggunaService)
    {
        return view('livewire.pengguna.index', [
            'pengguna' => $penggunaService->list(),
        ]);
    }
}
