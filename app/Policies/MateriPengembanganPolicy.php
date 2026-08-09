<?php

namespace App\Policies;

use App\Models\Pengguna;

/**
 * Katalog materi: seluruh peran boleh membaca (rujukan rekomendasi PD),
 * hanya Admin Dinas yang boleh CRUD - sama seperti InstrumenObservasiPolicy.
 */
class MateriPengembanganPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('pengembangan.manage');
    }

    public function view(Pengguna $pengguna): bool
    {
        return $pengguna->can('pengembangan.manage');
    }

    public function create(Pengguna $pengguna): bool
    {
        return $pengguna->can('pengembangan.manage') && $pengguna->hasRole('admin_dinas');
    }

    public function update(Pengguna $pengguna): bool
    {
        return $pengguna->can('pengembangan.manage') && $pengguna->hasRole('admin_dinas');
    }

    public function delete(Pengguna $pengguna): bool
    {
        return $pengguna->can('pengembangan.manage') && $pengguna->hasRole('admin_dinas');
    }
}
