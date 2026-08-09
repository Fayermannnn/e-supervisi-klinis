<?php

namespace App\Jobs;

use App\Modules\Notifikasi\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sprint 10 backlog: "Retry policy (3x backoff) job WA ... Kegagalan API
 * tidak permanen". Didispatch terpisah dari KirimReminderRtlJob (bukan
 * inline di dalamnya) - gagal di sini tidak pernah menyentuh notifikasi
 * in-app yang sudah tersimpan (Sprint 10 AC lain: "Kegagalan API tidak
 * gagalkan in-app").
 */
class KirimNotifikasiWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly string $nomorTujuan,
        public readonly string $pesan,
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $whatsAppService->kirim($this->nomorTujuan, $this->pesan);
    }
}
