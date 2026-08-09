<?php

namespace Tests\Feature\TindakLanjut;

use App\Jobs\KirimReminderRtlJob;
use App\Models\Notifikasi;
use App\Models\SesiSupervisi;
use App\Modules\Notifikasi\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KirimReminderRtlJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_mengirim_notifikasi_ke_supervisor(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        (new KirimReminderRtlJob($sesi->id))->handle(app(NotificationService::class));

        $this->assertDatabaseHas('notifikasi', [
            'pengguna_id' => $sesi->supervisor_id,
            'judul' => 'Pengingat RTL Tertunda',
        ]);
    }

    public function test_job_tidak_mengirim_reminder_duplikat(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $notificationService = app(NotificationService::class);

        (new KirimReminderRtlJob($sesi->id))->handle($notificationService);
        (new KirimReminderRtlJob($sesi->id))->handle($notificationService);

        $this->assertSame(1, Notifikasi::where('pengguna_id', $sesi->supervisor_id)
            ->where('judul', 'Pengingat RTL Tertunda')
            ->count());
    }

    public function test_job_aman_dijalankan_untuk_sesi_yang_sudah_dihapus(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $sesiId = $sesi->id;
        $sesi->delete();

        (new KirimReminderRtlJob($sesiId))->handle(app(NotificationService::class));

        $this->assertDatabaseCount('notifikasi', 0);
    }
}
