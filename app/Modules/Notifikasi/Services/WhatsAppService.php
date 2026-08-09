<?php

namespace App\Modules\Notifikasi\Services;

use Illuminate\Support\Facades\Http;

/**
 * Sprint 10 (Modul 12): integrasi Fonnte. Method ini SENGAJA membiarkan
 * kegagalan HTTP melempar exception (Http::throw()) - "Kegagalan API tidak
 * gagalkan in-app" (Sprint 10 AC) ditegakkan dengan menjalankan pengiriman
 * ini di job terpisah (KirimNotifikasiWhatsAppJob) yang didispatch SETELAH
 * notifikasi in-app tersimpan, bukan dengan menelan exception di sini -
 * exception yang melempar keluar justru dibutuhkan supaya retry policy job
 * (3x backoff) bisa bekerja.
 */
class WhatsAppService
{
    public function kirim(string $nomorTujuan, string $pesan): void
    {
        Http::asForm()
            ->withHeaders(['Authorization' => config('services.fonnte.token')])
            ->post(config('services.fonnte.url'), [
                'target' => $nomorTujuan,
                'message' => $pesan,
            ])
            ->throw();
    }
}
