<?php

namespace Tests\Feature\RBAC;

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAndPermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_5_roles(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertTrue(Role::where('name', 'guru')->exists());
        $this->assertTrue(Role::where('name', 'supervisor')->exists());
        $this->assertTrue(Role::where('name', 'kepala_sekolah')->exists());
        $this->assertTrue(Role::where('name', 'admin_dinas')->exists());
        $this->assertTrue(Role::where('name', 'super_admin')->exists());
    }

    public function test_seeder_creates_mvp_permissions(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertTrue(Permission::where('name', 'sekolah.manage')->exists());
        $this->assertTrue(Permission::where('name', 'pengguna.manage')->exists());
    }

    public function test_admin_dinas_can_manage_sekolah_and_pengguna(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $role = Role::findByName('admin_dinas');

        $this->assertTrue($role->hasPermissionTo('sekolah.manage'));
        $this->assertTrue($role->hasPermissionTo('pengguna.manage'));
    }

    public function test_kepala_sekolah_can_only_manage_pengguna(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $role = Role::findByName('kepala_sekolah');

        $this->assertTrue($role->hasPermissionTo('pengguna.manage'));
        $this->assertFalse($role->hasPermissionTo('sekolah.manage'));
    }

    public function test_guru_and_supervisor_have_no_management_permissions(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertCount(0, Role::findByName('guru')->permissions);
        $this->assertCount(0, Role::findByName('supervisor')->permissions);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertSame(2, Permission::count());
    }
}
