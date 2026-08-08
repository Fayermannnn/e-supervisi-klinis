<?php

namespace App\Livewire\Sekolah;

use App\Models\Sekolah;
use App\Modules\Sekolah\Services\SekolahService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Sekolah')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Sekolah::class);
    }

    public function hapus(string $id, SekolahService $sekolahService): void
    {
        $this->authorize('delete', $sekolahService->find($id));

        $sekolahService->delete($id);

        session()->flash('status', 'Sekolah berhasil dihapus.');
    }

    public function render(SekolahService $sekolahService)
    {
        return view('livewire.sekolah.index', [
            'sekolah' => $sekolahService->list(),
        ]);
    }
}
