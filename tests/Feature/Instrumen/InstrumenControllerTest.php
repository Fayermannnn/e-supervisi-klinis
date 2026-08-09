<?php

namespace Tests\Feature\Instrumen;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Models\Pengguna;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class InstrumenControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_instrumen_endpoints(): void
    {
        $this->getJson('/api/v1/instrumen-observasi')->assertStatus(401);
    }

    public function test_guru_can_view_instrumen_list(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('guru'));
        InstrumenObservasi::factory()->count(2)->create();

        $this->getJson('/api/v1/instrumen-observasi')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_guru_cannot_create_instrumen(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('guru'));

        $this->postJson('/api/v1/instrumen-observasi', ['versi' => 1, 'nama' => 'X'])
            ->assertStatus(403);
    }

    public function test_admin_dinas_can_create_instrumen(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));

        $response = $this->postJson('/api/v1/instrumen-observasi', ['versi' => 1, 'nama' => 'Instrumen Uji']);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama', 'Instrumen Uji')
            ->assertJsonPath('data.terkunci', false);
    }

    public function test_admin_dinas_cannot_update_terkunci_instrumen(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));

        $instrumen = InstrumenObservasi::factory()->terkunci()->create();

        $response = $this->patchJson("/api/v1/instrumen-observasi/{$instrumen->id}", ['nama' => 'Baru']);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.code', 'BR06_INSTRUMEN_TERKUNCI');
    }

    public function test_admin_dinas_can_aktifkan_instrumen(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));

        $instrumen = InstrumenObservasi::factory()->create();

        $this->patchJson("/api/v1/instrumen-observasi/{$instrumen->id}/aktifkan")
            ->assertStatus(200)
            ->assertJsonPath('data.terkunci', true);
    }

    public function test_admin_dinas_can_tambah_butir(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));

        $instrumen = InstrumenObservasi::factory()->create();

        $response = $this->postJson("/api/v1/instrumen-observasi/{$instrumen->id}/butir", [
            'kode' => 'A1',
            'dimensi' => 'A. Pembukaan Pembelajaran',
            'teks' => 'Guru membuka pembelajaran dengan salam',
            'bobot' => 5,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.kode', 'A1');
    }

    public function test_admin_dinas_cannot_hapus_butir_dari_instrumen_terkunci(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));

        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->deleteJson("/api/v1/butir-observasi/{$butir->id}")
            ->assertStatus(422)
            ->assertJsonPath('errors.0.code', 'BR06_INSTRUMEN_TERKUNCI');
    }

    public function test_pengguna_without_permission_cannot_access_instrumen_endpoints(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        Sanctum::actingAs(Pengguna::factory()->create());

        $this->getJson('/api/v1/instrumen-observasi')
            ->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }
}
