<?php

namespace Tests\Feature\PengembanganProfesional;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Models\MateriPengembangan;
use App\Models\SesiSupervisi;
use App\Modules\Observasi\Services\HasilObservasiService;
use App\Modules\TindakLanjut\Services\RtlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifikasi BR-09a/BR-09b terpicu lewat alur nyata (RtlService /
 * HasilObservasiService -> Event -> Listener), bukan hanya memanggil
 * RekomendasiService langsung.
 */
class Br09TriggerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_isi_rtl_memicu_rekomendasi_otomatis_lewat_event(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $materi = MateriPengembangan::factory()->create(['kategori' => 'sosial']);

        app(RtlService::class)->isiRtl($sesi->id, [
            'deskripsi' => 'Tingkatkan interaksi sosial di kelas',
            'target_waktu' => now()->addWeek()->toDateString(),
            'kategori' => 'sosial',
        ]);

        $this->assertDatabaseHas('rekomendasi_pengembangan', [
            'pengguna_id' => $sesi->guru_id,
            'materi_id' => $materi->id,
            'sumber' => 'otomatis_rtl',
        ]);
    }

    public function test_finalisasi_observasi_memicu_rekomendasi_otomatis_lewat_event(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $materi = MateriPengembangan::factory()->create();
        DB::table('butir_observasi_materi')->insert(['butir_observasi_id' => $butir->id, 'materi_id' => $materi->id]);

        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);

        app(HasilObservasiService::class)->simpanHasil($sesi->id, [
            'butir' => [['butir_id' => $butir->id, 'skor' => 1]],
            'status_akhir' => true,
        ]);

        $this->assertDatabaseHas('rekomendasi_pengembangan', [
            'pengguna_id' => $sesi->guru_id,
            'materi_id' => $materi->id,
            'sumber' => 'otomatis_observasi',
        ]);
    }
}
