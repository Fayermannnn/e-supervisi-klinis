<?php

namespace Tests\Feature\Analisis;

use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use App\Modules\Analisis\Services\SkorCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-ANALISIS-001: rumus berbobot dicocokkan terhadap perhitungan manual
 * (skenario A-C) plus kasus tepi skor_min/skor_maks (D-E), sesuai DoD
 * Sprint 6 backlog.
 */
class SkorCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private SkorCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(SkorCalculator::class);
    }

    /**
     * Skenario A (manual): 1 butir, bobot 100%, skor_maks 4, skor 4 (maks).
     * skor_tertimbang = (4/4) x 100 = 100. Total = 100.
     */
    public function test_skenario_a_satu_butir_skor_maksimal(): void
    {
        $instrumen = InstrumenObservasi::factory()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 4]);

        $hasil = $this->calculator->hitung($sesi->fresh());

        $this->assertSame(100.0, $hasil['total']);
        $this->assertSame(100.0, $hasil['breakdown'][0]['skor_tertimbang']);
    }

    /**
     * Skenario B (manual): 1 butir, bobot 100%, skor_maks 4, skor 1 (min).
     * skor_tertimbang = (1/4) x 100 = 25. Total = 25.
     */
    public function test_skenario_b_satu_butir_skor_minimal(): void
    {
        $instrumen = InstrumenObservasi::factory()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 1]);

        $hasil = $this->calculator->hitung($sesi->fresh());

        $this->assertSame(25.0, $hasil['total']);
    }

    /**
     * Skenario C (manual): 2 butir bobot 50% masing-masing, skor_maks 4,
     * skor 4 dan 2. st1 = (4/4)x50 = 50. st2 = (2/4)x50 = 25. Total = 75.
     */
    public function test_skenario_c_dua_butir_skor_campuran(): void
    {
        $instrumen = InstrumenObservasi::factory()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $butir1 = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 50]);
        $butir2 = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 50]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir1->id, 'skor' => 4]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir2->id, 'skor' => 2]);

        $hasil = $this->calculator->hitung($sesi->fresh());

        $this->assertSame(75.0, $hasil['total']);
    }

    /**
     * Kasus tepi D: skor persis di skor_min instrumen.
     */
    public function test_kasus_tepi_skor_persis_skor_min(): void
    {
        $instrumen = InstrumenObservasi::factory()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 40]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => $instrumen->skor_min]);

        $hasil = $this->calculator->hitung($sesi->fresh());

        $this->assertSame(10.0, $hasil['total']); // (1/4) x 40 = 10
    }

    /**
     * Kasus tepi E: skor persis di skor_maks instrumen -> bobot penuh.
     */
    public function test_kasus_tepi_skor_persis_skor_maks(): void
    {
        $instrumen = InstrumenObservasi::factory()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 40]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => $instrumen->skor_maks]);

        $hasil = $this->calculator->hitung($sesi->fresh());

        $this->assertSame(40.0, $hasil['total']); // (4/4) x 40 = 40 (bobot penuh)
    }

    public function test_sesi_tanpa_instrumen_mengembalikan_total_nol(): void
    {
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => null]);

        $hasil = $this->calculator->hitung($sesi);

        $this->assertSame(0.0, $hasil['total']);
        $this->assertSame([], $hasil['breakdown']);
    }

    public function test_sesi_tanpa_hasil_observasi_mengembalikan_total_nol(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);

        $hasil = $this->calculator->hitung($sesi);

        $this->assertSame(0.0, $hasil['total']);
    }
}
