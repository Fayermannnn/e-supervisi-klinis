<?php

namespace App\Livewire\Perencanaan;

use App\Models\SesiSupervisi;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Sesi Supervisi')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', SesiSupervisi::class);
    }

    public function render(SesiSupervisiService $sesiSupervisiService)
    {
        $actor = auth()->user();

        return view('livewire.perencanaan.index', [
            'sesi' => $sesiSupervisiService->list(
                guruId: $actor->hasRole('guru') ? $actor->id : null,
                supervisorId: $actor->hasRole('supervisor') ? $actor->id : null,
                sekolahId: $actor->hasRole('kepala_sekolah') ? $actor->sekolah_id : null,
            ),
        ]);
    }
}
