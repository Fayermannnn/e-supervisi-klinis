<?php

namespace App\Jobs;

use App\Mail\PengingatRtlMail;
use App\Models\Notifikasi;
use App\Models\SesiSupervisi;
use App\Modules\Notifikasi\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Sprint 8 backlog: "Kegagalan pertama tidak permanen" - retry 3x dengan
 * backoff, supaya gangguan sesaat (mis. DB lock) tidak menggagalkan
 * reminder yang menjadi mekanisme utama mencegah B-04 (RTL tertunda)
 * terulang di produksi. Sprint 10 menambah kanal email (Mail::queue(),
 * job mail terpisah bawaan Laravel) dan WhatsApp (KirimNotifikasiWhatsAppJob,
 * didispatch terpisah supaya kegagalan WA tidak menggagalkan notifikasi
 * in-app yang sudah tersimpan di bawah).
 */
class KirimReminderRtlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly string $sesiId) {}

    public function handle(NotificationService $notificationService): void
    {
        $sesi = SesiSupervisi::with(['supervisor', 'guru'])->find($this->sesiId);

        if (! $sesi || ! $sesi->supervisor) {
            return;
        }

        $tautan = route('app.sesi-supervisi.rtl', $sesi, absolute: false);

        $sudahDiingatkan = Notifikasi::where('pengguna_id', $sesi->supervisor_id)
            ->where('judul', 'Pengingat RTL Tertunda')
            ->where('tautan', $tautan)
            ->exists();

        if ($sudahDiingatkan) {
            return;
        }

        $pesan = "RTL untuk sesi supervisi {$sesi->guru->nama} pada {$sesi->tanggal->format('d/m/Y')} belum diisi, lebih dari 3 hari sejak umpan balik diberikan.";

        $notificationService->kirim(
            penerima: $sesi->supervisor,
            judul: 'Pengingat RTL Tertunda',
            pesan: $pesan,
            tautan: $tautan,
        );

        Mail::to($sesi->supervisor->email)->queue(new PengingatRtlMail($sesi));

        if ($sesi->supervisor->no_telepon) {
            KirimNotifikasiWhatsAppJob::dispatch($sesi->supervisor->no_telepon, $pesan);
        }
    }
}
