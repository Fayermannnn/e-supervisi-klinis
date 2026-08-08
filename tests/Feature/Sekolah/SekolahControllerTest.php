<?php

namespace Tests\Feature\Sekolah;

use App\Models\Pengguna;
use App\Models\Sekolah;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class SekolahControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    private function actingAsPengguna(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));
    }

    public function test_guest_cannot_access_sekolah_endpoints(): void
    {
        $response = $this->getJson('/api/v1/sekolah');

        $response->assertStatus(401);
    }

    public function test_pengguna_without_permission_cannot_access_sekolah_endpoints(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        Sanctum::actingAs(Pengguna::factory()->create());

        $response = $this->getJson('/api/v1/sekolah');

        $response->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }

    public function test_index_returns_paginated_sekolah(): void
    {
        $this->actingAsPengguna();

        Sekolah::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/sekolah');

        $response->assertStatus(200)
            ->assertJsonPath('errors', null)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_store_creates_sekolah(): void
    {
        $this->actingAsPengguna();

        $response = $this->postJson('/api/v1/sekolah', [
            'nama_sekolah' => 'SDN 1 Sidoarjo',
            'npsn' => '20501234',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama_sekolah', 'SDN 1 Sidoarjo')
            ->assertJsonPath('data.npsn', '20501234')
            ->assertJsonPath('data.status_aktif', true);

        $this->assertDatabaseHas('sekolah', ['npsn' => '20501234']);
    }

    public function test_store_requires_nama_sekolah(): void
    {
        $this->actingAsPengguna();

        $response = $this->postJson('/api/v1/sekolah', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama_sekolah']);
    }

    public function test_store_rejects_duplicate_npsn(): void
    {
        $this->actingAsPengguna();

        Sekolah::factory()->create(['npsn' => '20501234']);

        $response = $this->postJson('/api/v1/sekolah', [
            'nama_sekolah' => 'SDN 2 Sidoarjo',
            'npsn' => '20501234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['npsn']);
    }

    public function test_show_returns_sekolah(): void
    {
        $this->actingAsPengguna();

        $sekolah = Sekolah::factory()->create();

        $response = $this->getJson("/api/v1/sekolah/{$sekolah->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $sekolah->id);
    }

    public function test_show_returns_envelope_error_when_not_found(): void
    {
        $this->actingAsPengguna();

        $response = $this->getJson('/api/v1/sekolah/'.fake()->uuid());

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.code', 'SEKOLAH_NOT_FOUND');
    }

    public function test_update_modifies_sekolah(): void
    {
        $this->actingAsPengguna();

        $sekolah = Sekolah::factory()->create(['nama_sekolah' => 'SDN Lama']);

        $response = $this->putJson("/api/v1/sekolah/{$sekolah->id}", [
            'nama_sekolah' => 'SDN Baru',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_sekolah', 'SDN Baru');

        $this->assertDatabaseHas('sekolah', ['id' => $sekolah->id, 'nama_sekolah' => 'SDN Baru']);
    }

    public function test_update_returns_envelope_error_when_not_found(): void
    {
        $this->actingAsPengguna();

        $response = $this->putJson('/api/v1/sekolah/'.fake()->uuid(), [
            'nama_sekolah' => 'SDN Baru',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.code', 'SEKOLAH_NOT_FOUND');
    }

    public function test_destroy_soft_deletes_sekolah(): void
    {
        $this->actingAsPengguna();

        $sekolah = Sekolah::factory()->create();

        $response = $this->deleteJson("/api/v1/sekolah/{$sekolah->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('sekolah', ['id' => $sekolah->id]);
    }

    public function test_destroy_returns_envelope_error_when_not_found(): void
    {
        $this->actingAsPengguna();

        $response = $this->deleteJson('/api/v1/sekolah/'.fake()->uuid());

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.code', 'SEKOLAH_NOT_FOUND');
    }
}
