<?php

namespace Tests\Feature\Perencanaan;

use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class SesiSupervisiControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_sesi_supervisi_endpoints(): void
    {
        $response = $this->getJson('/api/v1/sesi-supervisi');

        $response->assertStatus(401);
    }

    public function test_pengguna_without_permission_cannot_access_sesi_supervisi_endpoints(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        Sanctum::actingAs(Pengguna::factory()->create());

        $response = $this->getJson('/api/v1/sesi-supervisi');

        $response->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }

    public function test_supervisor_can_create_jadwal(): void
    {
        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);
        $supervisor = $this->penggunaWithRole('supervisor');
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/sesi-supervisi', [
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'dijadwalkan')
            ->assertJsonPath('data.sekolah_id', $sekolah->id);
    }

    public function test_guru_cannot_create_jadwal(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $lainGuru = $this->penggunaWithRole('guru');
        Sanctum::actingAs($guru);

        $response = $this->postJson('/api/v1/sesi-supervisi', [
            'guru_id' => $lainGuru->id,
            'supervisor_id' => $guru->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_store_rejects_supervisor_sama_dengan_guru(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/sesi-supervisi', [
            'guru_id' => $supervisor->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.code', 'BR02_SUPERVISOR_EQUALS_GURU');
    }

    /**
     * TC-PERENCANAAN-003 (BR-05): akses data individual dibatasi
     * guru/supervisor terkait/kepsek/Admin Dinas - guru hanya melihat
     * sesinya sendiri.
     */
    public function test_guru_can_view_own_sesi_but_not_others(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesiSendiri = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        $sesiOrangLain = SesiSupervisi::factory()->create();
        Sanctum::actingAs($guru);

        $this->getJson("/api/v1/sesi-supervisi/{$sesiSendiri->id}")->assertStatus(200);
        $this->getJson("/api/v1/sesi-supervisi/{$sesiOrangLain->id}")->assertStatus(403);
    }

    /**
     * TC-PERENCANAAN-003 (BR-05): Kepala Sekolah hanya melihat sesi di
     * sekolahnya sendiri, ditolak untuk sekolah lain.
     */
    public function test_kepala_sekolah_can_view_sesi_di_sekolahnya_tapi_tidak_sekolah_lain(): void
    {
        $sekolahSendiri = Sekolah::factory()->create();
        $sekolahLain = Sekolah::factory()->create();
        $kepsek = $this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolahSendiri->id]);
        $sesiDiSekolahSendiri = SesiSupervisi::factory()->create(['sekolah_id' => $sekolahSendiri->id]);
        $sesiDiSekolahLain = SesiSupervisi::factory()->create(['sekolah_id' => $sekolahLain->id]);
        Sanctum::actingAs($kepsek);

        $this->getJson("/api/v1/sesi-supervisi/{$sesiDiSekolahSendiri->id}")->assertStatus(200);
        $this->getJson("/api/v1/sesi-supervisi/{$sesiDiSekolahLain->id}")->assertStatus(403);
    }

    /**
     * TC-PERENCANAAN-004 (BR-07): tipe_supervisor hanya menerima
     * internal/eksternal, nilai lain ditolak.
     */
    public function test_store_menolak_tipe_supervisor_yang_tidak_valid(): void
    {
        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);
        $supervisor = $this->penggunaWithRole('supervisor');
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/sesi-supervisi', [
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'bukan_nilai_valid',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('tipe_supervisor');
    }

    /**
     * TC-PERENCANAAN-004 (BR-07): nilai valid "internal" diterima dan
     * tersimpan apa adanya.
     */
    public function test_store_menerima_tipe_supervisor_internal(): void
    {
        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);
        $supervisor = $this->penggunaWithRole('supervisor');
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/sesi-supervisi', [
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(201)->assertJsonPath('data.tipe_supervisor', 'internal');
    }

    /**
     * TC-PERENCANAAN-004 (BR-07): nilai valid "eksternal" diterima dan
     * tersimpan apa adanya.
     */
    public function test_store_menerima_tipe_supervisor_eksternal(): void
    {
        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);
        $supervisor = $this->penggunaWithRole('supervisor');
        Sanctum::actingAs($supervisor);

        $response = $this->postJson('/api/v1/sesi-supervisi', [
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'eksternal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(201)->assertJsonPath('data.tipe_supervisor', 'eksternal');
    }

    public function test_supervisor_can_isi_pra_observasi_untuk_sesi_yang_ditangani(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'status' => 'dijadwalkan']);
        Sanctum::actingAs($supervisor);

        $response = $this->patchJson("/api/v1/sesi-supervisi/{$sesi->id}/pra-observasi", [
            'fokus_observasi' => 'Strategi pembelajaran diferensiasi',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'pra_observasi');
    }

    public function test_supervisor_cannot_isi_pra_observasi_untuk_sesi_supervisor_lain(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['status' => 'dijadwalkan']);
        Sanctum::actingAs($supervisor);

        $response = $this->patchJson("/api/v1/sesi-supervisi/{$sesi->id}/pra-observasi", [
            'fokus_observasi' => 'Strategi pembelajaran diferensiasi',
        ]);

        $response->assertStatus(403);
    }
}
