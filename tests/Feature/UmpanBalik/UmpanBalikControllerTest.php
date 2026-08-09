<?php

namespace Tests\Feature\UmpanBalik;

use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class UmpanBalikControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    private array $dataDasar = [
        'kekuatan' => 'Penguasaan materi kuat.',
        'area_pengembangan' => 'Manajemen waktu.',
        'rekomendasi' => 'Gunakan pengatur waktu.',
    ];

    public function test_guest_cannot_access_umpan_balik_endpoints(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik", $this->dataDasar)
            ->assertStatus(401);
    }

    public function test_supervisor_can_isi_umpan_balik_untuk_sesi_yang_ditangani(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik", $this->dataDasar);

        $response->assertStatus(201)
            ->assertJsonPath('data.kekuatan', 'Penguasaan materi kuat.')
            ->assertJsonPath('data.redacted', false);
    }

    public function test_guru_tidak_bisa_membuat_umpan_balik(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        Sanctum::actingAs($guru);

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik", $this->dataDasar)
            ->assertStatus(403);
    }

    public function test_guru_bisa_melihat_umpan_balik_miliknya_secara_utuh(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'kekuatan' => 'Rahasia formatif']);
        Sanctum::actingAs($guru);

        $response = $this->getJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik");

        $response->assertStatus(200)
            ->assertJsonPath('data.kekuatan', 'Rahasia formatif')
            ->assertJsonPath('data.redacted', false);
    }

    /**
     * TC-UMPANBALIK-002 (BR-08): Admin Dinas mendapat field diredaksi
     * eksplisit ("redacted": true), bukan 403 diam-diam.
     */
    public function test_admin_dinas_melihat_umpan_balik_dengan_field_sensitif_diredaksi(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create([
            'sesi_id' => $sesi->id,
            'kekuatan' => 'Rahasia formatif',
            'area_pengembangan' => 'Detail sensitif',
            'rekomendasi' => 'Saran personal',
        ]);
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik");

        $response->assertStatus(200)
            ->assertJsonPath('data.redacted', true)
            ->assertJsonPath('data.kekuatan', null)
            ->assertJsonPath('data.area_pengembangan', null)
            ->assertJsonPath('data.rekomendasi', null);
    }

    /**
     * TC-UMPANBALIK-003 (BR-10): endpoint pendekatan menerima nilai yang
     * berbeda dari pendekatan_disarankan tanpa ditolak.
     */
    public function test_supervisor_bisa_ubah_pendekatan_menyimpang_dari_saran(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'pendekatan_disarankan' => 'directive_control']);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->patchJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik/pendekatan", [
            'pendekatan_dipakai' => 'nondirective',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.pendekatan_dipakai', 'nondirective');
    }

    public function test_guru_bisa_isi_refleksi_untuk_sesinya_sendiri(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);
        Sanctum::actingAs($guru);

        $response = $this->patchJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik/refleksi", [
            'refleksi_guru' => 'Saya setuju dengan masukan ini.',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.refleksi_guru', 'Saya setuju dengan masukan ini.');
    }

    public function test_supervisor_tidak_bisa_isi_refleksi(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);
        Sanctum::actingAs($supervisor);

        $this->patchJson("/api/v1/sesi-supervisi/{$sesi->id}/umpan-balik/refleksi", [
            'refleksi_guru' => 'Mencoba mengisi refleksi.',
        ])->assertStatus(403);
    }
}
