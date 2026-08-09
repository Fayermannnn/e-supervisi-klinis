<?php

namespace App\Console\Commands;

use App\Jobs\KirimReminderRtlJob;
use App\Models\SesiSupervisi;
use Illuminate\Console\Command;

/**
 * Sprint 8 backlog: "Scheduler job harian cek RTL tertunda ... Reminder
 * otomatis >3 hari". RTL "tertunda" = umpan balik sudah diberikan
 * (tanggal_diisi tersedia sebagai penanda waktu yang andal) namun RTL
 * belum diisi sama sekali - begitu RTL diisi, sesi tidak lagi masuk
 * kriteria ini meskipun belum ditandai selesai.
 */
class CekRtlTertunda extends Command
{
    protected $signature = 'rtl:cek-tertunda';

    protected $description = 'Kirim reminder untuk sesi dengan RTL tertunda lebih dari 3 hari sejak umpan balik diberikan';

    public function handle(): int
    {
        $batasWaktu = now()->subDays(3);

        $sesiTertunda = SesiSupervisi::query()
            ->whereHas('umpanBalik', fn ($query) => $query->where('tanggal_diisi', '<=', $batasWaktu))
            ->whereDoesntHave('rtl')
            ->get();

        foreach ($sesiTertunda as $sesi) {
            KirimReminderRtlJob::dispatch($sesi->id);
        }

        $this->info("Reminder RTL didispatch untuk {$sesiTertunda->count()} sesi.");

        return self::SUCCESS;
    }
}
