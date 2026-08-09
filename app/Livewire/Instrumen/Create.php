<?php

namespace App\Livewire\Instrumen;

use App\Models\InstrumenObservasi;
use App\Modules\Instrumen\Services\InstrumenService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Buat Instrumen')]
class Create extends Component
{
    public ?int $versi = null;

    public string $nama = '';

    public function mount(): void
    {
        $this->authorize('create', InstrumenObservasi::class);
    }

    public function simpan(InstrumenService $instrumenService): void
    {
        $data = $this->validate([
            'versi' => ['required', 'integer', 'min:1', 'unique:instrumen_observasi,versi'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $instrumen = $instrumenService->create($data);

        session()->flash('status', 'Instrumen berhasil dibuat. Tambahkan butir sebelum mengaktifkannya.');

        $this->redirect(route('app.instrumen.kelola', $instrumen));
    }

    public function render()
    {
        return view('livewire.instrumen.create');
    }
}
