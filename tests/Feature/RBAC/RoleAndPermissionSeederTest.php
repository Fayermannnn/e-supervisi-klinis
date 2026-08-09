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
        $this->assertTrue(Permission::where('name', 'sesi-supervisi.manage')->exists());
        $this->assertTrue(Permission::where('name', 'notifikasi.manage')->exists());
        $this->assertTrue(Permission::where('name', 'instrumen.manage')->exists());
        $this->assertTrue(Permission::where('name', 'pengembangan.manage')->exists());
        $this->assertTrue(Permission::where('name', 'laporan.manage')->exists());
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

    public function test_guru_and_supervisor_cannot_manage_sekolah_or_pengguna(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $guru = Role::findByName('guru');
        $supervisor = Role::findByName('supervisor');

        $this->assertFalse($guru->hasPermissionTo('sekolah.manage'));
        $this->assertFalse($guru->hasPermissionTo('pengguna.manage'));
        $this->assertFalse($supervisor->hasPermissionTo('sekolah.manage'));
        $this->assertFalse($supervisor->hasPermissionTo('pengguna.manage'));
    }

    public function test_guru_and_supervisor_can_reach_sesi_supervisi_and_notifikasi(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $guru = Role::findByName('guru');
        $supervisor = Role::findByName('supervisor');

        $this->assertTrue($guru->hasPermissionTo('sesi-supervisi.manage'));
        $this->assertTrue($guru->hasPermissionTo('notifikasi.manage'));
        $this->assertTrue($supervisor->hasPermissionTo('sesi-supervisi.manage'));
        $this->assertTrue($supervisor->hasPermissionTo('notifikasi.manage'));
    }

    public function test_guru_and_supervisor_can_view_instrumen_but_not_manage_it(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $guru = Role::findByName('guru');
        $supervisor = Role::findByName('supervisor');

        $this->assertTrue($guru->hasPermissionTo('instrumen.manage'));
        $this->assertTrue($supervisor->hasPermissionTo('instrumen.manage'));
    }

    public function test_guru_and_supervisor_can_reach_pengembangan(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $guru = Role::findByName('guru');
        $supervisor = Role::findByName('supervisor');

        $this->assertTrue($guru->hasPermissionTo('pengembangan.manage'));
        $this->assertTrue($supervisor->hasPermissionTo('pengembangan.manage'));
    }

    /**
     * Sprint 9: laporan.manage berbeda dari modul lain - Guru/Supervisor
     * tidak pernah mengakses Pelaporan.
     */
    public function test_hanya_kepala_sekolah_dan_admin_dinas_bisa_reach_laporan(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertTrue(Role::findByName('kepala_sekolah')->hasPermissionTo('laporan.manage'));
        $this->assertTrue(Role::findByName('admin_dinas')->hasPermissionTo('laporan.manage'));
        $this->assertTrue(Role::findByName('super_admin')->hasPermissionTo('laporan.manage'));
        $this->assertFalse(Role::findByName('guru')->hasPermissionTo('laporan.manage'));
        $this->assertFalse(Role::findByName('supervisor')->hasPermissionTo('laporan.manage'));
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertSame(7, Permission::count());
    }
}
