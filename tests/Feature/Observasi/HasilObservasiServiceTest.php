<?php

namespace Tests\Feature\Observasi;

use App\Exceptions\ButirTidakSesuaiInstrumenException;
use App\Exceptions\HasilObservasiBelumLengkapException;
use App\Exceptions\InstrumenAktifTidakTersediaException;
use App\Exceptions\SkorDiLuarRentangException;
use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use App\Modules\Observasi\Services\HasilObservasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasilObservasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private HasilObservasiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HasilObservasiService::class);
    }

    public function test_simpan_hasil_menolak_jika_tidak_ada_instrumen_aktif(): void
    {
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => null]);
        $instrumen = InstrumenObservasi::factory()->create(); // belum terkunci
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->expectException(InstrumenAktifTidakTersediaException::class);

        $this->service->simpanHasil($sesi->id, ['butir' => [['butir_id' => $butir->id, 'skor' => 3]]]);
    }

    public function test_simpan_hasil_mengisi_instrumen_id_otomatis_dari_instrumen_terkunci(): void
    {
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => null]);
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $updated = $this->service->simpanHasil($sesi->id, ['butir' => [['butir_id' => $butir->id, 'skor' => 3]]]);

        $this->assertSame($instrumen->id, $updated->instrumen_id);
        $this->assertSame('observasi', $updated->status);
    }

    public function test_simpan_hasil_menolak_butir_dari_instrumen_lain(): void
    {
        $instrumenAktif = InstrumenObservasi::factory()->terkunci()->create();
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumenAktif->id]);
        $butirInstrumenLain = ButirObservasi::factory()->create();

        $this->expectException(ButirTidakSesuaiInstrumenException::class);

        $this->service->simpanHasil($sesi->id, ['butir' => [['butir_id' => $butirInstrumenLain->id, 'skor' => 3]]]);
    }

    public function test_simpan_hasil_menolak_skor_di_luar_rentang_instrumen(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create(['skor_min' => 1, 'skor_maks' => 4]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->expectException(SkorDiLuarRentangException::class);

        $this->service->simpanHasil($sesi->id, ['butir' => [['butir_id' => $butir->id, 'skor' => 5]]]);
    }

    public function test_simpan_hasil_draft_mengizinkan_sebagian_butir_saja(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        ButirObservasi::factory()->count(3)->create(['instrumen_id' => $instrumen->id]);
        $satuButir = $instrumen->butir()->first();

        $updated = $this->service->simpanHasil($sesi->id, [
            'butir' => [['butir_id' => $satuButir->id, 'skor' => 2]],
        ]);

        $this->assertSame('observasi', $updated->status);
        $this->assertSame(1, $updated->hasilObservasi()->count());
    }

    public function test_simpan_hasil_final_menolak_jika_belum_semua_butir_terisi(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        $butir = ButirObservasi::factory()->count(2)->create(['instrumen_id' => $instrumen->id]);

        $this->expectException(HasilObservasiBelumLengkapException::class);

        $this->service->simpanHasil($sesi->id, [
            'butir' => [['butir_id' => $butir->first()->id, 'skor' => 2]],
            'status_akhir' => true,
        ]);
    }

    public function test_simpan_hasil_final_mengubah_status_sesi_menjadi_dianalisis(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $updated = $this->service->simpanHasil($sesi->id, [
            'butir' => [['butir_id' => $butir->id, 'skor' => 4]],
            'status_akhir' => true,
        ]);

        $this->assertSame('dianalisis', $updated->status);
    }

    public function test_simpan_hasil_upsert_tidak_duplikat_saat_disimpan_ulang(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->service->simpanHasil($sesi->id, ['butir' => [['butir_id' => $butir->id, 'skor' => 2]]]);
        $updated = $this->service->simpanHasil($sesi->id, ['butir' => [['butir_id' => $butir->id, 'skor' => 4]]]);

        $this->assertSame(1, $updated->hasilObservasi()->count());
        $this->assertSame(4, $updated->hasilObservasi()->first()->skor);
    }
}
