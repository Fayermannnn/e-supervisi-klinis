<?php

namespace App\Livewire\Pengguna;

use App\Models\Pengguna as PenggunaModel;
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

    public function mount(): void
    {
        $this->authorize('viewAny', PenggunaModel::class);
    }

    public function hapus(string $id, PenggunaService $penggunaService): void
    {
        $target = $penggunaService->find($id);

        $this->authorize('delete', $target);

        $penggunaService->delete($id);

        session()->flash('status', 'Pengguna berhasil dihapus.');
    }

    public function render(PenggunaService $penggunaService)
    {
        /** @var PenggunaModel $actor */
        $actor = auth()->user();
        $sekolahId = $actor->hasRole('kepala_sekolah') ? $actor->sekolah_id : null;

        return view('livewire.pengguna.index', [
            'pengguna' => $penggunaService->list(sekolahId: $sekolahId),
        ]);
    }
}
