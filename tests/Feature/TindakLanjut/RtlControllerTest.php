<?php

namespace Tests\Feature\TindakLanjut;

use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class RtlControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_rtl_endpoints(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/rtl", [])->assertStatus(401);
    }

    public function test_supervisor_can_isi_rtl_untuk_sesi_yang_ditangani(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/rtl", [
            'deskripsi' => 'Ikuti pelatihan',
            'target_waktu' => now()->addWeek()->toDateString(),
            'kategori' => 'pedagogik',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.deskripsi', 'Ikuti pelatihan');
    }

    public function test_guru_tidak_bisa_membuat_rtl(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        Sanctum::actingAs($guru);

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/rtl", [
            'deskripsi' => 'Coba buat RTL',
            'target_waktu' => now()->addWeek()->toDateString(),
        ])->assertStatus(403);
    }

    /**
     * TC-RTL-001 (BR-04): endpoint selesaikan menolak dengan error code
     * eksplisit RTL_REQUIRED_BEFORE_CLOSE bila RTL kosong.
     */
    public function test_selesaikan_menolak_dengan_kode_error_eksplisit_bila_rtl_kosong(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/selesaikan");

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.code', 'RTL_REQUIRED_BEFORE_CLOSE');
    }

    public function test_selesaikan_berhasil_setelah_rtl_diisi(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/selesaikan");

        $response->assertStatus(200)->assertJsonPath('data.status', 'selesai');
    }

    public function test_guru_bisa_melihat_rtl_miliknya(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id, 'deskripsi' => 'Rencana saya']);
        Sanctum::actingAs($guru);

        $response = $this->getJson("/api/v1/sesi-supervisi/{$sesi->id}/rtl");

        $response->assertStatus(200)->assertJsonPath('data.deskripsi', 'Rencana saya');
    }
}
