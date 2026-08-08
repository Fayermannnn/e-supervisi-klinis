<?php

namespace Tests\Feature\RBAC;

use App\Models\Pengguna;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_one_super_admin_account(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $pengguna = Pengguna::where('email', 'superadmin@sidoarjo.go.id')->first();

        $this->assertNotNull($pengguna);
        $this->assertTrue($pengguna->hasRole('super_admin'));
    }

    public function test_seeder_does_not_duplicate_existing_account(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SuperAdminSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->assertSame(1, Pengguna::where('email', 'superadmin@sidoarjo.go.id')->count());
    }
}
