<?php

namespace Tests\Feature\PengembanganProfesional;

use App\Models\MateriPengembangan;
use App\Models\Pengguna;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class MateriControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_materi_endpoints(): void
    {
        $this->getJson('/api/v1/materi-pengembangan')->assertStatus(401);
    }

    public function test_guru_can_view_materi_list(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('guru'));
        MateriPengembangan::factory()->count(2)->create();

        $this->getJson('/api/v1/materi-pengembangan')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_guru_cannot_create_materi(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('guru'));

        $this->postJson('/api/v1/materi-pengembangan', [
            'judul' => 'X', 'kategori' => 'pedagogik',
        ])->assertStatus(403);
    }

    public function test_admin_dinas_can_create_materi(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));

        $response = $this->postJson('/api/v1/materi-pengembangan', [
            'judul' => 'Pelatihan Manajemen Kelas',
            'kategori' => 'pedagogik',
            'tautan_atau_deskripsi' => 'https://example.test/materi',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.judul', 'Pelatihan Manajemen Kelas');
    }

    public function test_admin_dinas_can_update_and_delete_materi(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));
        $materi = MateriPengembangan::factory()->create();

        $this->patchJson("/api/v1/materi-pengembangan/{$materi->id}", ['judul' => 'Baru'])
            ->assertStatus(200)->assertJsonPath('data.judul', 'Baru');

        $this->deleteJson("/api/v1/materi-pengembangan/{$materi->id}")->assertStatus(200);
        $this->assertSoftDeleted('materi_pengembangan', ['id' => $materi->id]);
    }

    public function test_pengguna_without_permission_cannot_access_materi_endpoints(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        Sanctum::actingAs(Pengguna::factory()->create());

        $this->getJson('/api/v1/materi-pengembangan')
            ->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }
}
