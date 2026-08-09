<?php

namespace Tests\Feature\Pelaporan;

use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\RencanaTindakLanjut;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use App\Modules\Pelaporan\Queries\LaporanAgregatQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-PELAPORAN-002 (dukungan TC-PELAPORAN-001): rekapSekolah() dan
 * dashboardAgregat() dicocokkan terhadap perhitungan manual, mengikuti
 * konvensi skenario manual SkorCalculatorTest (Sprint 6).
 */
class LaporanAgregatQueryTest extends TestCase
{
    use RefreshDatabase;

    private LaporanAgregatQuery $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = app(LaporanAgregatQuery::class);
    }

    /**
     * Manual: sesi1 skor=4 (tertimbang 100), sesi2 skor=2 (tertimbang 50),
     * sesi3 skor=1 (tertimbang 25) -> rata_rata_skor = (100+50+25)/3 = 58.33.
     * sesi4 tidak punya instrumen/hasil observasi -> ikut total_sesi tapi
     * tidak ikut rata-rata/tren. RTL: sesi1 selesai, sesi3 sedang -> 1/2
     * tuntas = 50%. sesi5 punya umpan_balik tanpa RTL -> tidak dihitung di
     * sini (rtl_tuntas_persen hanya menghitung sesi yang PUNYA rtl).
     */
    public function test_rekap_sekolah_sesuai_perhitungan_manual(): void
    {
        $sekolah = Sekolah::factory()->create();
        $instrumen = InstrumenObservasi::factory()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);

        $sesi1 = SesiSupervisi::factory()->create(['sekolah_id' => $sekolah->id, 'instrumen_id' => $instrumen->id, 'status' => 'selesai']);
        HasilObservasi::factory()->create(['sesi_id' => $sesi1->id, 'butir_id' => $butir->id, 'skor' => 4]);
        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi1->id, 'status' => 'selesai']);

        $sesi2 = SesiSupervisi::factory()->create(['sekolah_id' => $sekolah->id, 'instrumen_id' => $instrumen->id, 'status' => 'dianalisis']);
        HasilObservasi::factory()->create(['sesi_id' => $sesi2->id, 'butir_id' => $butir->id, 'skor' => 2]);

        $sesi3 = SesiSupervisi::factory()->create(['sekolah_id' => $sekolah->id, 'instrumen_id' => $instrumen->id, 'status' => 'rtl']);
        HasilObservasi::factory()->create(['sesi_id' => $sesi3->id, 'butir_id' => $butir->id, 'skor' => 1]);
        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi3->id, 'status' => 'sedang']);

        SesiSupervisi::factory()->create(['sekolah_id' => $sekolah->id, 'instrumen_id' => null, 'status' => 'draft']);

        $hasil = $this->query->rekapSekolah($sekolah->fresh());

        $this->assertSame(4, $hasil['total_sesi']);
        $this->assertSame(1, $hasil['sesi_selesai']);
        $this->assertSame(58.33, $hasil['rata_rata_skor']);
        $this->assertCount(3, $hasil['tren_skor']);
        $this->assertSame(50.0, $hasil['rtl_tuntas_persen']);
    }

    public function test_rekap_sekolah_lain_tidak_ikut_terhitung(): void
    {
        $sekolahA = Sekolah::factory()->create();
        $sekolahB = Sekolah::factory()->create();
        $instrumen = InstrumenObservasi::factory()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);

        $sesiB = SesiSupervisi::factory()->create(['sekolah_id' => $sekolahB->id, 'instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesiB->id, 'butir_id' => $butir->id, 'skor' => 4]);

        $hasil = $this->query->rekapSekolah($sekolahA->fresh());

        $this->assertSame(0, $hasil['total_sesi']);
        $this->assertSame(0.0, $hasil['rata_rata_skor']);
        $this->assertSame([], $hasil['tren_skor']);
    }

    /**
     * Manual: sekolahA rata-rata 100 (1 sesi), sekolahB rata-rata 50 (1
     * sesi) -> rata-rata kabupaten berbobot jumlah sesi = (100x1 + 50x1)/2
     * = 75. rtl_tertunda: 1 sesi (sekolahC) punya umpan_balik tanpa rtl.
     */
    public function test_dashboard_agregat_sesuai_perhitungan_manual(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);

        $sekolahA = Sekolah::factory()->create();
        $sesiA = SesiSupervisi::factory()->create(['sekolah_id' => $sekolahA->id, 'instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesiA->id, 'butir_id' => $butir->id, 'skor' => 4]);

        $sekolahB = Sekolah::factory()->create();
        $sesiB = SesiSupervisi::factory()->create(['sekolah_id' => $sekolahB->id, 'instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesiB->id, 'butir_id' => $butir->id, 'skor' => 2]);

        $sekolahC = Sekolah::factory()->create();
        $sesiC = SesiSupervisi::factory()->create(['sekolah_id' => $sekolahC->id, 'instrumen_id' => null]);
        UmpanBalik::factory()->create(['sesi_id' => $sesiC->id]);

        $hasil = $this->query->dashboardAgregat();

        $this->assertSame(3, $hasil['total_sekolah']);
        $this->assertSame(75.0, $hasil['rata_rata_skor_kabupaten']);
        $this->assertSame(1, $hasil['rtl_tertunda']);
        $this->assertCount(3, $hasil['skor_per_sekolah']);
    }
}
