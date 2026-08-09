<?php

namespace Tests\Feature\Instrumen;

use App\Exceptions\Br06InstrumenTerkunciException;
use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Modules\Instrumen\Services\InstrumenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstrumenServiceTest extends TestCase
{
    use RefreshDatabase;

    private InstrumenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InstrumenService::class);
    }

    public function test_create_membuat_instrumen_belum_terkunci(): void
    {
        $instrumen = $this->service->create(['versi' => 1, 'nama' => 'Instrumen Uji']);

        $this->assertFalse($instrumen->terkunci);
        $this->assertSame(1, $instrumen->skor_min);
        $this->assertSame(4, $instrumen->skor_maks);
    }

    /**
     * TC-INSTRUMEN-001 (BR-06): Instrumen aktif (terkunci) tidak bisa diedit.
     */
    public function test_update_menolak_instrumen_yang_sudah_terkunci(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();

        $this->expectException(Br06InstrumenTerkunciException::class);

        $this->service->update($instrumen->id, ['nama' => 'Nama Baru']);
    }

    public function test_update_mengizinkan_instrumen_yang_belum_terkunci(): void
    {
        $instrumen = InstrumenObservasi::factory()->create(['nama' => 'Lama']);

        $updated = $this->service->update($instrumen->id, ['nama' => 'Baru']);

        $this->assertSame('Baru', $updated->nama);
    }

    public function test_aktifkan_mengunci_instrumen(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();

        $updated = $this->service->aktifkan($instrumen->id);

        $this->assertTrue($updated->terkunci);
    }

    public function test_tambah_butir_menolak_instrumen_terkunci(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();

        $this->expectException(Br06InstrumenTerkunciException::class);

        $this->service->tambahButir($instrumen->id, [
            'kode' => 'A1', 'dimensi' => 'A. Pembukaan Pembelajaran', 'teks' => 'Teks', 'bobot' => 5,
        ]);
    }

    public function test_tambah_butir_berhasil_untuk_instrumen_belum_terkunci(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();

        $butir = $this->service->tambahButir($instrumen->id, [
            'kode' => 'A1', 'dimensi' => 'A. Pembukaan Pembelajaran', 'teks' => 'Teks', 'bobot' => 5,
        ]);

        $this->assertSame($instrumen->id, $butir->instrumen_id);
    }

    public function test_update_butir_menolak_jika_instrumen_induk_terkunci(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->expectException(Br06InstrumenTerkunciException::class);

        $this->service->updateButir($butir->id, ['teks' => 'Teks baru']);
    }

    public function test_hapus_butir_menolak_jika_instrumen_induk_terkunci(): void
    {
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->expectException(Br06InstrumenTerkunciException::class);

        $this->service->hapusButir($butir->id);
    }
}
