<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * 5 peran (RBAC Sprint 2) dan permission MVP untuk modul yang sudah ada
     * (Sekolah, Pengguna, Perencanaan Supervisi, Notifikasi). Middleware
     * RequirePermission memeriksa permission level-modul ini; scoping per
     * objek (mis. Kepala Sekolah hanya boleh mengelola pengguna di
     * sekolahnya sendiri, atau Guru hanya boleh melihat sesinya sendiri)
     * ditegakkan di Policy.
     */
    public function run(): void
    {
        $permissions = [
            'sekolah.manage',
            'pengguna.manage',
            'sesi-supervisi.manage',
            'notifikasi.manage',
            'instrumen.manage',
            'pengembangan.manage',
            'laporan.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = ['guru', 'supervisor', 'kepala_sekolah', 'admin_dinas', 'super_admin'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // instrumen.manage & pengembangan.manage: gerbang modul untuk semua peran
        // (CRUD Matrix - hanya Admin Dinas yang boleh C/U/D katalog, sisanya R saja
        // / kelola rekomendasi miliknya sendiri; dibedakan di Policy).
        // laporan.manage (Sprint 9): berbeda dari modul lain - Guru/Supervisor tidak
        // pernah mengakses Pelaporan (bukan bagian alur kerja harian mereka), hanya
        // Kepala Sekolah (Laporan Sekolah miliknya) dan Admin Dinas/Super Admin
        // (Dashboard Agregat + drill-down BR-08).
        Role::findByName('admin_dinas')->syncPermissions($permissions);
        Role::findByName('kepala_sekolah')->syncPermissions(['pengguna.manage', 'sesi-supervisi.manage', 'notifikasi.manage', 'instrumen.manage', 'pengembangan.manage', 'laporan.manage']);
        Role::findByName('super_admin')->syncPermissions($permissions);
        Role::findByName('guru')->syncPermissions(['sesi-supervisi.manage', 'notifikasi.manage', 'instrumen.manage', 'pengembangan.manage']);
        Role::findByName('supervisor')->syncPermissions(['sesi-supervisi.manage', 'notifikasi.manage', 'instrumen.manage', 'pengembangan.manage']);
    }
}
