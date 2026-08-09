<?php

namespace App\Livewire\Notifikasi;

use App\Modules\Notifikasi\Services\NotificationService;
use Livewire\Component;

class Badge extends Component
{
    public bool $terbuka = false;

    public function toggle(): void
    {
        $this->terbuka = ! $this->terbuka;
    }

    public function tandaiDibaca(string $notifikasiId, NotificationService $notificationService): void
    {
        $notifikasi = auth()->user()->notifikasi()->findOrFail($notifikasiId);

        $notificationService->tandaiDibaca($notifikasi);
    }

    public function render(NotificationService $notificationService)
    {
        $pengguna = auth()->user();

        return view('livewire.notifikasi.badge', [
            'jumlahBelumDibaca' => $notificationService->jumlahBelumDibaca($pengguna),
            'terbaru' => $this->terbuka ? $notificationService->terbaruUntuk($pengguna) : collect(),
        ]);
    }
}
