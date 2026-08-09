<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\Sekolah;

class SekolahPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('sekolah.manage');
    }

    /**
     * Sprint 9 (Modul 11, Laporan Sekolah): berbeda dari view() di atas
     * (yang digerbangi sekolah.manage, tidak dimiliki Kepala Sekolah).
     * Kepala Sekolah hanya boleh melihat laporan sekolahnya sendiri; Admin
     * Dinas/Super Admin boleh melihat laporan sekolah mana pun.
     */
    public function viewLaporan(Pengguna $pengguna, Sekolah $sekolah): bool
    {
        if (! $pengguna->can('laporan.manage')) {
            return false;
        }

        if ($pengguna->hasRole('kepala_sekolah')) {
            return $pengguna->sekolah_id !== null && $pengguna->sekolah_id === $sekolah->id;
        }

        return $pengguna->hasAnyRole(['admin_dinas', 'super_admin']);
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
