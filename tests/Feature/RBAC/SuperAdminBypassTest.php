<?php

namespace Tests\Feature\RBAC;

use App\Models\Pengguna;
use App\Models\Role;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_bypasses_gate_even_without_explicit_permission(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $pengguna = Pengguna::factory()->create();
        $pengguna->assignRole('super_admin');

        $this->assertTrue(Gate::forUser($pengguna)->allows('sekolah.manage'));
        $this->assertTrue(Gate::forUser($pengguna)->allows('permission.that.does.not.exist'));
    }

    public function test_super_admin_can_access_sekolah_endpoint_without_seeded_permission(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $pengguna = Pengguna::factory()->create();
        $pengguna->assignRole('super_admin');

        Sanctum::actingAs($pengguna);

        Sekolah::factory()->create();

        $response = $this->getJson('/api/v1/sekolah');

        $response->assertStatus(200);
    }
}
