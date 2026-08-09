<?php

namespace App\Livewire\Instrumen;

use App\Models\InstrumenObservasi;
use App\Modules\Instrumen\Services\InstrumenService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Instrumen')]
class Kelola extends Component
{
    public InstrumenObservasi $instrumen;

    public string $kode = '';

    public string $dimensi = '';

    public string $teks = '';

    public ?string $definisi_operasional = null;

    public ?float $bobot = null;

    public function mount(InstrumenObservasi $instrumen): void
    {
        $this->authorize('view', InstrumenObservasi::class);
        $this->instrumen = $instrumen;
    }

    public function tambahButir(InstrumenService $instrumenService): void
    {
        $this->authorize('update', InstrumenObservasi::class);

        $data = $this->validate([
            'kode' => ['required', 'string', 'max:10'],
            'dimensi' => ['required', 'string', 'max:255'],
            'teks' => ['required', 'string'],
            'definisi_operasional' => ['nullable', 'string'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $instrumenService->tambahButir($this->instrumen->id, $data);

        $this->reset(['kode', 'dimensi', 'teks', 'definisi_operasional', 'bobot']);
        $this->instrumen->refresh();

        session()->flash('status', 'Butir observasi berhasil ditambahkan.');
    }

    public function hapusButir(string $butirId, InstrumenService $instrumenService): void
    {
        $this->authorize('delete', InstrumenObservasi::class);

        $instrumenService->hapusButir($butirId);
        $this->instrumen->refresh();

        session()->flash('status', 'Butir observasi berhasil dihapus.');
    }

    public function aktifkan(InstrumenService $instrumenService): void
    {
        $this->authorize('update', InstrumenObservasi::class);

        $this->instrumen = $instrumenService->aktifkan($this->instrumen->id);

        session()->flash('status', 'Instrumen diaktifkan dan sekarang terkunci (BR-06).');
    }

    public function render()
    {
        return view('livewire.instrumen.kelola', [
            'butir' => $this->instrumen->butir()->orderBy('kode')->get(),
        ]);
    }
}
