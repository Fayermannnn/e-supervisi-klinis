<?php

namespace App\Policies;

use App\Models\Pengguna;

class SekolahPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('sekolah.manage');
    }

    public function view(Pengguna $pengguna): bool
    {
        return $pengguna->can('sekolah.manage');
    }

    public function create(Pengguna $pengguna): bool
    {
        return $pengguna->can('sekolah.manage');
    }

    public function update(Pengguna $pengguna): bool
    {
        return $pengguna->can('sekolah.manage');
    }

    public function delete(Pengguna $pengguna): bool
    {
        return $pengguna->can('sekolah.manage');
    }
}
