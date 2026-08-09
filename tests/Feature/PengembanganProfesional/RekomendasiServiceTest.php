<?php

namespace Tests\Feature\PengembanganProfesional;

use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\MateriPengembangan;
use App\Models\RekomendasiPengembangan;
use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use App\Modules\PengembanganProfesional\Services\RekomendasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * TC-PENGEMBANGAN-001 (BR-09 Dual Trigger, Addendum 02).
 */
class RekomendasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private RekomendasiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RekomendasiService::class);
    }

    public function test_br09a_picu_dari_rtl_mencocokkan_kategori(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $materiCocok = MateriPengembangan::factory()->create(['kategori' => 'pedagogik']);
        MateriPengembangan::factory()->create(['kategori' => 'sosial']); // tidak cocok

        $rtl = RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id, 'kategori' => 'pedagogik']);

        $hasil = $this->service->picuDariRtl($rtl);

        $this->assertCount(1, $hasil);
        $this->assertDatabaseHas('rekomendasi_pengembangan', [
            'pengguna_id' => $sesi->guru_id,
            'sesi_id' => $sesi->id,
            'materi_id' => $materiCocok->id,
            'sumber' => 'otomatis_rtl',
        ]);
    }

    public function test_br09a_tidak_duplikat_saat_dipanggil_ulang(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        MateriPengembangan::factory()->create(['kategori' => 'pedagogik']);
        $rtl = RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id, 'kategori' => 'pedagogik']);

        $this->service->picuDariRtl($rtl);
        $this->service->picuDariRtl($rtl);

        $this->assertSame(1, RekomendasiPengembangan::where('sesi_id', $sesi->id)->count());
    }

    /**
     * BR-09b (Addendum 02): skor butir <=2 pada hasil final memicu
     * rekomendasi lewat pemetaan butir->materi.
     */
    public function test_br09b_picu_dari_observasi_untuk_skor_rendah(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();
        $butirRendah = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $butirTinggi = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $materi = MateriPengembangan::factory()->create();

        DB::table('butir_observasi_materi')->insert([
            'butir_observasi_id' => $butirRendah->id,
            'materi_id' => $materi->id,
        ]);

        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butirRendah->id, 'skor' => 2]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butirTinggi->id, 'skor' => 4]);

        $hasil = $this->service->picuDariObservasi($sesi);

        $this->assertCount(1, $hasil);
        $this->assertDatabaseHas('rekomendasi_pengembangan', [
            'pengguna_id' => $sesi->guru_id,
            'sesi_id' => $sesi->id,
            'materi_id' => $materi->id,
            'sumber' => 'otomatis_observasi',
        ]);
    }

    public function test_br09b_tidak_memicu_untuk_skor_di_atas_ambang(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $materi = MateriPengembangan::factory()->create();
        DB::table('butir_observasi_materi')->insert(['butir_observasi_id' => $butir->id, 'materi_id' => $materi->id]);

        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 3]);

        $hasil = $this->service->picuDariObservasi($sesi);

        $this->assertCount(0, $hasil);
    }

    /**
     * BR-09a dan BR-09b independen - boleh menghasilkan rekomendasi
     * ganda untuk sesi & guru yang sama, tanpa deduplikasi lintas sumber
     * (Addendum 02, catatan implementasi eksplisit).
     */
    public function test_br09a_dan_br09b_independen_boleh_ganda(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $materi = MateriPengembangan::factory()->create(['kategori' => 'pedagogik']);
        DB::table('butir_observasi_materi')->insert(['butir_observasi_id' => $butir->id, 'materi_id' => $materi->id]);

        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 1]);
        $rtl = RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id, 'kategori' => 'pedagogik']);

        $this->service->picuDariObservasi($sesi);
        $this->service->picuDariRtl($rtl);

        $this->assertSame(2, RekomendasiPengembangan::where('sesi_id', $sesi->id)
            ->where('materi_id', $materi->id)
            ->count());
    }

    public function test_rekomendasikan_manual_membuat_rekomendasi_sumber_manual(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $materi = MateriPengembangan::factory()->create();

        $rekomendasi = $this->service->rekomendasikanManual([
            'pengguna_id' => $sesi->guru_id,
            'sesi_id' => $sesi->id,
            'materi_id' => $materi->id,
        ]);

        $this->assertSame('manual', $rekomendasi->sumber);
    }

    public function test_ubah_status_memperbarui_status_rekomendasi(): void
    {
        $rekomendasi = RekomendasiPengembangan::factory()->create();

        $updated = $this->service->ubahStatus($rekomendasi->id, 'sedang');

        $this->assertSame('sedang', $updated->status);
    }
}
