<?php

namespace Tests\Feature\Pelaporan;

use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class LaporanControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_laporan_endpoints(): void
    {
        $sekolah = Sekolah::factory()->create();

        $this->getJson("/api/v1/laporan/sekolah/{$sekolah->id}")->assertStatus(401);
        $this->getJson('/api/v1/laporan/dashboard')->assertStatus(401);
    }

    public function test_guru_tidak_punya_akses_laporan_sama_sekali(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sekolah = Sekolah::factory()->create();
        Sanctum::actingAs($guru);

        $this->getJson("/api/v1/laporan/sekolah/{$sekolah->id}")->assertStatus(403);
        $this->getJson('/api/v1/laporan/dashboard')->assertStatus(403);
    }

    public function test_kepala_sekolah_bisa_melihat_laporan_sekolahnya_sendiri(): void
    {
        $sekolah = Sekolah::factory()->create();
        $kepsek = $this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolah->id]);
        Sanctum::actingAs($kepsek);

        $response = $this->getJson("/api/v1/laporan/sekolah/{$sekolah->id}");

        $response->assertStatus(200)->assertJsonPath('data.sekolah_id', $sekolah->id);
    }

    public function test_kepala_sekolah_tidak_bisa_melihat_laporan_sekolah_lain(): void
    {
        $sekolahSendiri = Sekolah::factory()->create();
        $sekolahLain = Sekolah::factory()->create();
        $kepsek = $this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolahSendiri->id]);
        Sanctum::actingAs($kepsek);

        $this->getJson("/api/v1/laporan/sekolah/{$sekolahLain->id}")->assertStatus(403);
    }

    public function test_kepala_sekolah_tidak_bisa_melihat_dashboard_agregat(): void
    {
        $kepsek = $this->penggunaWithRole('kepala_sekolah');
        Sanctum::actingAs($kepsek);

        $this->getJson('/api/v1/laporan/dashboard')->assertStatus(403);
    }

    public function test_admin_dinas_bisa_melihat_laporan_sekolah_mana_pun(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        $sekolah = Sekolah::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/laporan/sekolah/{$sekolah->id}")->assertStatus(200);
    }

    public function test_admin_dinas_bisa_melihat_dashboard_agregat(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/laporan/dashboard');

        $response->assertStatus(200)->assertJsonStructure(['data' => ['total_sekolah', 'skor_per_sekolah']]);
    }

    /**
     * TC-PELAPORAN-001 (BR-08): drill-down individual ditolak tanpa
     * justifikasi.
     */
    public function test_drill_down_ditolak_tanpa_justifikasi(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        $sesi = SesiSupervisi::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/laporan/individual/{$sesi->id}", [])
            ->assertStatus(422);
    }

    /**
     * TC-PELAPORAN-001 (BR-08): drill-down berhasil dengan justifikasi,
     * data utuh (tidak diredaksi) dikembalikan, dan tercatat di audit_log.
     */
    public function test_admin_dinas_drill_down_dengan_justifikasi_tercatat_di_audit_log(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'kekuatan' => 'Rahasia formatif']);
        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/v1/laporan/individual/{$sesi->id}", [
            'justifikasi' => 'Audit rutin triwulan atas permintaan Kepala Dinas.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.sesi_id', $sesi->id)
            ->assertJsonPath('data.umpan_balik.kekuatan', 'Rahasia formatif');

        $this->assertDatabaseHas('audit_log', [
            'pengguna_id' => $admin->id,
            'aksi' => 'LAPORAN_DRILL_DOWN_INDIVIDUAL',
            'model_type' => SesiSupervisi::class,
            'model_id' => $sesi->id,
        ]);
    }

    public function test_kepala_sekolah_tidak_bisa_drill_down_individual(): void
    {
        $kepsek = $this->penggunaWithRole('kepala_sekolah');
        $sesi = SesiSupervisi::factory()->create();
        Sanctum::actingAs($kepsek);

        $this->postJson("/api/v1/laporan/individual/{$sesi->id}", [
            'justifikasi' => 'Mencoba mengakses tanpa hak.',
        ])->assertStatus(403);
    }
}
