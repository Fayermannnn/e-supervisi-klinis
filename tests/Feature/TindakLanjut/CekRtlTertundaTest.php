<?php

namespace Tests\Feature\TindakLanjut;

use App\Jobs\KirimReminderRtlJob;
use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CekRtlTertundaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mendispatch_reminder_untuk_sesi_dengan_rtl_tertunda_lebih_dari_3_hari(): void
    {
        Bus::fake();

        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'tanggal_diisi' => now()->subDays(5)]);

        $this->artisan('rtl:cek-tertunda')->assertSuccessful();

        Bus::assertDispatched(KirimReminderRtlJob::class, fn ($job) => $job->sesiId === $sesi->id);
    }

    public function test_tidak_mendispatch_untuk_sesi_dalam_3_hari(): void
    {
        Bus::fake();

        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'tanggal_diisi' => now()->subDay()]);

        $this->artisan('rtl:cek-tertunda');

        Bus::assertNotDispatched(KirimReminderRtlJob::class);
    }

    public function test_tidak_mendispatch_untuk_sesi_yang_rtl_nya_sudah_diisi(): void
    {
        Bus::fake();

        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'tanggal_diisi' => now()->subDays(5)]);
        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id]);

        $this->artisan('rtl:cek-tertunda');

        Bus::assertNotDispatched(KirimReminderRtlJob::class);
    }

    public function test_tidak_mendispatch_untuk_sesi_tanpa_umpan_balik(): void
    {
        Bus::fake();

        SesiSupervisi::factory()->create();

        $this->artisan('rtl:cek-tertunda');

        Bus::assertNotDispatched(KirimReminderRtlJob::class);
    }
}
