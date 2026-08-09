<?php

namespace App\Livewire\PengembanganProfesional;

use App\Modules\PengembanganProfesional\Services\RekomendasiService;
use Livewire\Component;

class WidgetRekomendasiGuru extends Component
{
    public function ubahStatus(string $id, string $status, RekomendasiService $rekomendasiService): void
    {
        $rekomendasi = $rekomendasiService->find($id);
        $this->authorize('ubahStatus', $rekomendasi);

        $rekomendasiService->ubahStatus($id, $status);
    }

    public function render(RekomendasiService $rekomendasiService)
    {
        return view('livewire.pengembanganprofesional.widget-rekomendasi-guru', [
            'rekomendasi' => $rekomendasiService->untukPengguna(auth()->id(), perPage: 5),
        ]);
    }
}
