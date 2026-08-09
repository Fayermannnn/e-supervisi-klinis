<?php

namespace App\Policies;

use App\Models\Pengguna;

/**
 * CRUD Matrix (Dok 04, Tahap 3): Guru/Supervisor/Kepala Sekolah - R saja,
 * Admin Dinas - CRUD, Super Admin - R (bypass via Gate::before).
 */
class InstrumenObservasiPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('instrumen.manage');
    }

    public function view(Pengguna $pengguna): bool
    {
        return $pengguna->can('instrumen.manage');
    }

    public function create(Pengguna $pengguna): bool
    {
        return $pengguna->can('instrumen.manage') && $pengguna->hasRole('admin_dinas');
    }

    public function update(Pengguna $pengguna): bool
    {
        return $pengguna->can('instrumen.manage') && $pengguna->hasRole('admin_dinas');
    }

    public function delete(Pengguna $pengguna): bool
    {
        return $pengguna->can('instrumen.manage') && $pengguna->hasRole('admin_dinas');
    }
}
