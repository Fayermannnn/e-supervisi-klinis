<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * 5 peran (RBAC Sprint 2) dan permission MVP untuk modul yang sudah ada
     * (Sekolah, Pengguna). Middleware RequirePermission memeriksa permission
     * level-modul ini; scoping per objek (mis. Kepala Sekolah hanya boleh
     * mengelola pengguna di sekolahnya sendiri) ditegakkan di Policy.
     */
    public function run(): void
    {
        $permissions = [
            'sekolah.manage',
            'pengguna.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = ['guru', 'supervisor', 'kepala_sekolah', 'admin_dinas', 'super_admin'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Role::findByName('admin_dinas')->syncPermissions($permissions);
        Role::findByName('kepala_sekolah')->syncPermissions(['pengguna.manage']);
        Role::findByName('super_admin')->syncPermissions($permissions);
    }
}
