<?php

namespace App\Listeners;

use App\Events\SesiSupervisiDijadwalkan;
use App\Modules\Notifikasi\Services\NotificationService;

class KirimNotifikasiJadwalDibuat
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(SesiSupervisiDijadwalkan $event): void
    {
        $sesi = $event->sesiSupervisi;
        $tanggal = $sesi->tanggal->format('d/m/Y');

        $this->notificationService->kirim(
            penerima: $sesi->guru,
            judul: 'Jadwal Supervisi Baru',
            pesan: "Anda dijadwalkan untuk sesi supervisi pada {$tanggal}.",
            tautan: route('app.sesi-supervisi.index', absolute: false),
        );

        $this->notificationService->kirim(
            penerima: $sesi->supervisor,
            judul: 'Jadwal Supervisi Baru',
            pesan: "Anda menjadwalkan sesi supervisi pada {$tanggal}.",
            tautan: route('app.sesi-supervisi.index', absolute: false),
        );
    }
}
