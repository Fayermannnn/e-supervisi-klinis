<?php

namespace Tests\Feature\TindakLanjut;

use App\Jobs\KirimNotifikasiWhatsAppJob;
use App\Jobs\KirimReminderRtlJob;
use App\Mail\PengingatRtlMail;
use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use App\Modules\Notifikasi\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
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

    /**
     * Sprint 10: kanal email ditambahkan di samping in-app, dikirim via
     * Mail::queue() (job mail terpisah bawaan Laravel).
     */
    public function test_job_mengantrekan_email_pengingat_ke_supervisor(): void
    {
        Mail::fake();
        $sesi = SesiSupervisi::factory()->create();

        (new KirimReminderRtlJob($sesi->id))->handle(app(NotificationService::class));

        Mail::assertQueued(PengingatRtlMail::class, fn ($mail) => $mail->hasTo($sesi->supervisor->email) && $mail->sesi->id === $sesi->id);
    }

    /**
     * Sprint 10: kanal WhatsApp didispatch sebagai job terpisah
     * (KirimNotifikasiWhatsAppJob) supaya kegagalannya tidak menyentuh
     * notifikasi in-app yang sudah tersimpan.
     */
    public function test_job_mendispatch_notifikasi_whatsapp_saat_supervisor_punya_nomor_telepon(): void
    {
        Bus::fake();
        $sesi = SesiSupervisi::factory()->create();

        (new KirimReminderRtlJob($sesi->id))->handle(app(NotificationService::class));

        Bus::assertDispatched(KirimNotifikasiWhatsAppJob::class, fn ($job) => $job->nomorTujuan === $sesi->supervisor->no_telepon);
    }

    public function test_job_tidak_mendispatch_whatsapp_bila_supervisor_tanpa_nomor_telepon(): void
    {
        Bus::fake();
        $supervisor = Pengguna::factory()->create(['no_telepon' => null]);
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);

        (new KirimReminderRtlJob($sesi->id))->handle(app(NotificationService::class));

        Bus::assertNotDispatched(KirimNotifikasiWhatsAppJob::class);
    }
}
