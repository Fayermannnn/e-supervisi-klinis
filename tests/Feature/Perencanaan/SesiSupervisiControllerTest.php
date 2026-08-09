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

    public function test_guru_can_view_own_sesi_but_not_others(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesiSendiri = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        $sesiOrangLain = SesiSupervisi::factory()->create();
        Sanctum::actingAs($guru);

        $this->getJson("/api/v1/sesi-supervisi/{$sesiSendiri->id}")->assertStatus(200);
        $this->getJson("/api/v1/sesi-supervisi/{$sesiOrangLain->id}")->assertStatus(403);
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
