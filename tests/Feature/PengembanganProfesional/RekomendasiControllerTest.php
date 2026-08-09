<?php

namespace Tests\Feature\PengembanganProfesional;

use App\Models\MateriPengembangan;
use App\Models\RekomendasiPengembangan;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class RekomendasiControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_supervisor_can_rekomendasikan_manual_untuk_sesi_yang_ditangani(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $materi = MateriPengembangan::factory()->create();
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/rekomendasi", [
            'materi_id' => $materi->id,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.sumber', 'manual');
    }

    public function test_supervisor_tidak_bisa_rekomendasikan_untuk_sesi_orang_lain(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create();
        $materi = MateriPengembangan::factory()->create();
        Sanctum::actingAs($supervisor);

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/rekomendasi", ['materi_id' => $materi->id])
            ->assertStatus(403);
    }

    public function test_guru_bisa_melihat_daftar_rekomendasi_miliknya_sendiri(): void
    {
        $guru = $this->penggunaWithRole('guru');
        RekomendasiPengembangan::factory()->count(2)->create(['pengguna_id' => $guru->id]);
        Sanctum::actingAs($guru);

        $this->getJson("/api/v1/pengguna/{$guru->id}/rekomendasi")
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_guru_tidak_bisa_melihat_rekomendasi_guru_lain(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $guruLain = $this->penggunaWithRole('guru');
        Sanctum::actingAs($guru);

        $this->getJson("/api/v1/pengguna/{$guruLain->id}/rekomendasi")->assertStatus(403);
    }

    public function test_guru_bisa_ubah_status_rekomendasi_miliknya(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $rekomendasi = RekomendasiPengembangan::factory()->create(['pengguna_id' => $guru->id]);
        Sanctum::actingAs($guru);

        $response = $this->patchJson("/api/v1/rekomendasi/{$rekomendasi->id}/status", ['status' => 'selesai']);

        $response->assertStatus(200)->assertJsonPath('data.status', 'selesai');
    }

    public function test_guru_tidak_bisa_ubah_status_rekomendasi_orang_lain(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $rekomendasi = RekomendasiPengembangan::factory()->create();
        Sanctum::actingAs($guru);

        $this->patchJson("/api/v1/rekomendasi/{$rekomendasi->id}/status", ['status' => 'selesai'])
            ->assertStatus(403);
    }
}
