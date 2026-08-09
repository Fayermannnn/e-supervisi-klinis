<?php

namespace Tests\Feature\Perencanaan;

use App\Exceptions\Br01FokusObservasiKosongException;
use App\Exceptions\Br02SupervisorEqualsGuruException;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class SesiSupervisiServiceTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    private SesiSupervisiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SesiSupervisiService::class);
    }

    /**
     * TC-PERENCANAAN-001 (BR-02): Supervisor tidak boleh sama dengan guru
     * yang diobservasi.
     */
    public function test_buat_jadwal_menolak_supervisor_sama_dengan_guru(): void
    {
        $orang = $this->penggunaWithRole('supervisor');

        $this->expectException(Br02SupervisorEqualsGuruException::class);

        $this->service->buatJadwal([
            'guru_id' => $orang->id,
            'supervisor_id' => $orang->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);
    }

    public function test_buat_jadwal_berhasil_membuat_sesi_berstatus_dijadwalkan(): void
    {
        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);
        $supervisor = $this->penggunaWithRole('supervisor');

        $sesi = $this->service->buatJadwal([
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $this->assertSame('dijadwalkan', $sesi->status);
        $this->assertSame($sekolah->id, $sesi->sekolah_id);
        $this->assertDatabaseHas('sesi_supervisi', ['id' => $sesi->id, 'status' => 'dijadwalkan']);
    }

    /**
     * TC-PERENCANAAN-002 (BR-01): Status hanya berubah menjadi pra_observasi
     * bila fokus observasi terisi.
     */
    public function test_isi_pra_observasi_menolak_fokus_kosong(): void
    {
        $sesi = SesiSupervisi::factory()->create(['status' => 'dijadwalkan']);

        $this->expectException(Br01FokusObservasiKosongException::class);

        $this->service->isiPraObservasi($sesi->id, ['fokus_observasi' => '']);
    }

    public function test_isi_pra_observasi_mengubah_status_ketika_fokus_terisi(): void
    {
        $sesi = SesiSupervisi::factory()->create(['status' => 'dijadwalkan']);

        $updated = $this->service->isiPraObservasi($sesi->id, [
            'fokus_observasi' => 'Manajemen kelas dan keterlibatan siswa',
        ]);

        $this->assertSame('pra_observasi', $updated->status);
        $this->assertSame('Manajemen kelas dan keterlibatan siswa', $updated->fokus_observasi);
    }

    public function test_isi_pra_observasi_memetakan_level_ke_pendekatan_disarankan(): void
    {
        $sesi = SesiSupervisi::factory()->create(['status' => 'dijadwalkan']);

        $updated = $this->service->isiPraObservasi($sesi->id, [
            'fokus_observasi' => 'Diferensiasi pembelajaran',
            'level_perkembangan_guru' => 'rendah',
        ]);

        $this->assertSame('rendah', $updated->level_perkembangan_guru);
        $this->assertSame('directive_control', $updated->pendekatan_disarankan);
    }

    public function test_isi_pra_observasi_tidak_memblokir_submit_tanpa_level(): void
    {
        $sesi = SesiSupervisi::factory()->create(['status' => 'dijadwalkan']);

        $updated = $this->service->isiPraObservasi($sesi->id, [
            'fokus_observasi' => 'Asesmen formatif',
        ]);

        $this->assertSame('pra_observasi', $updated->status);
        $this->assertNull($updated->level_perkembangan_guru);
        $this->assertNull($updated->pendekatan_disarankan);
    }
}
