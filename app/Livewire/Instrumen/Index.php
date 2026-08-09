<?php

namespace App\Livewire\Instrumen;

use App\Models\InstrumenObservasi;
use App\Modules\Instrumen\Services\InstrumenService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Instrumen Observasi')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', InstrumenObservasi::class);
    }

    public function render(InstrumenService $instrumenService)
    {
        return view('livewire.instrumen.index', [
            'instrumen' => $instrumenService->list(),
        ]);
    }
}
