<?php

namespace App\Policies;

use App\Models\Pengguna;

/**
 * Sprint 10: "Super Admin telusuri tanpa edit/hapus" - berbeda dari modul
 * lain, admin_dinas TIDAK ikut mendapat akses meskipun biasanya permission
 * admin_dinas dan super_admin identik (lihat RoleAndPermissionSeeder -
 * audit-log.manage sengaja tidak disertakan di grant admin_dinas).
 * super_admin sendiri sudah otomatis lolos lewat Gate::before di
 * AppServiceProvider, jadi method ini tidak perlu mengecek perannya secara
 * eksplisit - cukup gerbang permission untuk peran lain.
 */
class AuditLogPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('audit-log.manage');
    }
}
