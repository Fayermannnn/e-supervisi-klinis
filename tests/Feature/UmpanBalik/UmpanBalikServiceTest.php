<?php

namespace Tests\Feature\UmpanBalik;

use App\Exceptions\UmpanBalikSudahAdaException;
use App\Models\SesiSupervisi;
use App\Modules\UmpanBalik\Services\UmpanBalikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmpanBalikServiceTest extends TestCase
{
    use RefreshDatabase;

    private UmpanBalikService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UmpanBalikService::class);
    }

    private array $dataDasar = [
        'kekuatan' => 'Penguasaan materi kuat, interaksi siswa aktif.',
        'area_pengembangan' => 'Manajemen waktu perlu diperbaiki.',
        'rekomendasi' => 'Gunakan pengatur waktu visual di kelas.',
    ];

    /**
     * TC-UMPANBALIK-001 (BR-03): umpan balik dalam 7 hari TIDAK ditandai terlambat.
     */
    public function test_umpan_balik_dalam_7_hari_tidak_terlambat(): void
    {
        $sesi = SesiSupervisi::factory()->create(['tanggal' => now()->subDays(3)]);

        $umpanBalik = $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);

        $this->assertFalse($umpanBalik->terlambat);
    }

    /**
     * TC-UMPANBALIK-001 (BR-03): umpan balik lewat 7 hari TETAP tersimpan
     * (tidak diblokir), hanya ditandai terlambat.
     */
    public function test_umpan_balik_lewat_7_hari_ditandai_terlambat_tapi_tidak_diblokir(): void
    {
        $sesi = SesiSupervisi::factory()->create(['tanggal' => now()->subDays(10)]);

        $umpanBalik = $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);

        $this->assertTrue($umpanBalik->terlambat);
        $this->assertDatabaseHas('umpan_balik', ['sesi_id' => $sesi->id]);
    }

    public function test_isi_umpan_balik_memindahkan_status_sesi(): void
    {
        $sesi = SesiSupervisi::factory()->create(['tanggal' => now()]);

        $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);

        $this->assertSame('umpan_balik', $sesi->fresh()->status);
    }

    public function test_tidak_bisa_mengisi_umpan_balik_dua_kali(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);

        $this->expectException(UmpanBalikSudahAdaException::class);

        $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);
    }

    /**
     * TC-UMPANBALIK-003 (BR-10): pendekatan_dipakai boleh menyimpang dari
     * pendekatan_disarankan tanpa ditolak.
     */
    public function test_ubah_pendekatan_boleh_menyimpang_dari_saran(): void
    {
        $sesi = SesiSupervisi::factory()->create(['pendekatan_disarankan' => 'directive_control']);
        $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);

        $umpanBalik = $this->service->ubahPendekatan($sesi->id, 'nondirective');

        $this->assertSame('nondirective', $umpanBalik->pendekatan_dipakai);
    }

    public function test_isi_refleksi_menyimpan_refleksi_guru(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $this->service->isiUmpanBalik($sesi->id, $this->dataDasar);

        $umpanBalik = $this->service->isiRefleksi($sesi->id, 'Saya akan mencoba rekomendasi ini minggu depan.');

        $this->assertSame('Saya akan mencoba rekomendasi ini minggu depan.', $umpanBalik->refleksi_guru);
    }
}
