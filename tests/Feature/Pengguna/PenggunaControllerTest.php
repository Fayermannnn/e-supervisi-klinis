<?php

namespace Tests\Feature\Pengguna;

use App\Models\Pengguna;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class PenggunaControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    private function actingAsPengguna(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('admin_dinas'));
    }

    public function test_guest_cannot_access_pengguna_endpoints(): void
    {
        $response = $this->getJson('/api/v1/pengguna');

        $response->assertStatus(401);
    }

    public function test_pengguna_without_permission_cannot_access_pengguna_endpoints(): void
    {
        Sanctum::actingAs($this->penggunaWithRole('guru'));

        $response = $this->getJson('/api/v1/pengguna');

        $response->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }

    public function test_kepala_sekolah_only_sees_pengguna_in_own_sekolah(): void
    {
        $sekolahSendiri = Sekolah::factory()->create();
        $sekolahLain = Sekolah::factory()->create();

        Sanctum::actingAs($this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolahSendiri->id]));

        Pengguna::factory()->count(2)->create(['sekolah_id' => $sekolahSendiri->id]);
        Pengguna::factory()->count(3)->create(['sekolah_id' => $sekolahLain->id]);

        $response = $this->getJson('/api/v1/pengguna');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 3); // 2 + akun kepala sekolah sendiri
    }

    public function test_kepala_sekolah_cannot_create_pengguna_for_other_sekolah(): void
    {
        $sekolahSendiri = Sekolah::factory()->create();
        $sekolahLain = Sekolah::factory()->create();

        Sanctum::actingAs($this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolahSendiri->id]));

        $response = $this->postJson('/api/v1/pengguna', [
            'sekolah_id' => $sekolahLain->id,
            'nama' => 'Guru Sekolah Lain',
            'email' => 'guru.lain@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }

    public function test_kepala_sekolah_cannot_view_pengguna_from_other_sekolah(): void
    {
        $sekolahSendiri = Sekolah::factory()->create();
        $sekolahLain = Sekolah::factory()->create();

        Sanctum::actingAs($this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolahSendiri->id]));

        $targetLain = Pengguna::factory()->create(['sekolah_id' => $sekolahLain->id]);

        $response = $this->getJson("/api/v1/pengguna/{$targetLain->id}");

        $response->assertStatus(403)
            ->assertJsonPath('errors.0.code', 'FORBIDDEN');
    }

    public function test_index_returns_paginated_pengguna(): void
    {
        $this->actingAsPengguna();

        Pengguna::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/pengguna');

        $response->assertStatus(200)
            ->assertJsonPath('errors', null)
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.total', 4);
    }

    public function test_index_response_hides_password(): void
    {
        $this->actingAsPengguna();

        $response = $this->getJson('/api/v1/pengguna');

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.0.password');
    }

    public function test_store_creates_pengguna_with_hashed_password(): void
    {
        $this->actingAsPengguna();

        $sekolah = Sekolah::factory()->create();

        $response = $this->postJson('/api/v1/pengguna', [
            'sekolah_id' => $sekolah->id,
            'nama' => 'Budi Santoso',
            'email' => 'budi@sidoarjo.go.id',
            'password' => 'rahasia123',
            'nip_nuptk' => '1234567890',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama', 'Budi Santoso')
            ->assertJsonPath('data.email', 'budi@sidoarjo.go.id')
            ->assertJsonPath('data.status_aktif', true)
            ->assertJsonMissingPath('data.password');

        $pengguna = Pengguna::where('email', 'budi@sidoarjo.go.id')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $pengguna->password));
    }

    public function test_store_requires_nama_email_and_password(): void
    {
        $this->actingAsPengguna();

        $response = $this->postJson('/api/v1/pengguna', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama', 'email', 'password']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        $this->actingAsPengguna();

        Pengguna::factory()->create(['email' => 'dup@sidoarjo.go.id']);

        $response = $this->postJson('/api/v1/pengguna', [
            'nama' => 'Duplikat',
            'email' => 'dup@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_rejects_unknown_sekolah_id(): void
    {
        $this->actingAsPengguna();

        $response = $this->postJson('/api/v1/pengguna', [
            'nama' => 'Budi Santoso',
            'email' => 'budi2@sidoarjo.go.id',
            'password' => 'rahasia123',
            'sekolah_id' => fake()->uuid(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sekolah_id']);
    }

    public function test_show_returns_pengguna(): void
    {
        $this->actingAsPengguna();

        $pengguna = Pengguna::factory()->create();

        $response = $this->getJson("/api/v1/pengguna/{$pengguna->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $pengguna->id);
    }

    public function test_show_returns_envelope_error_when_not_found(): void
    {
        $this->actingAsPengguna();

        $response = $this->getJson('/api/v1/pengguna/'.fake()->uuid());

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.code', 'PENGGUNA_NOT_FOUND');
    }

    public function test_update_modifies_pengguna_without_touching_password(): void
    {
        $this->actingAsPengguna();

        $pengguna = Pengguna::factory()->create(['nama' => 'Nama Lama']);
        $originalPassword = $pengguna->password;

        $response = $this->putJson("/api/v1/pengguna/{$pengguna->id}", [
            'nama' => 'Nama Baru',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama', 'Nama Baru');

        $this->assertSame($originalPassword, $pengguna->fresh()->password);
    }

    public function test_update_can_change_password(): void
    {
        $this->actingAsPengguna();

        $pengguna = Pengguna::factory()->create();

        $response = $this->putJson("/api/v1/pengguna/{$pengguna->id}", [
            'password' => 'sandibaru123',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('sandibaru123', $pengguna->fresh()->password));
    }

    public function test_update_returns_envelope_error_when_not_found(): void
    {
        $this->actingAsPengguna();

        $response = $this->putJson('/api/v1/pengguna/'.fake()->uuid(), [
            'nama' => 'Nama Baru',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.code', 'PENGGUNA_NOT_FOUND');
    }

    public function test_destroy_soft_deletes_pengguna(): void
    {
        $this->actingAsPengguna();

        $pengguna = Pengguna::factory()->create();

        $response = $this->deleteJson("/api/v1/pengguna/{$pengguna->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('pengguna', ['id' => $pengguna->id]);
    }

    public function test_destroy_returns_envelope_error_when_not_found(): void
    {
        $this->actingAsPengguna();

        $response = $this->deleteJson('/api/v1/pengguna/'.fake()->uuid());

        $response->assertStatus(404)
            ->assertJsonPath('errors.0.code', 'PENGGUNA_NOT_FOUND');
    }
}
