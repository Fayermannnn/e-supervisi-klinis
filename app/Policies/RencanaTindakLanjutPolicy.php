<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;

/**
 * CRUD Matrix (Dok 04): Guru R (miliknya), Supervisor CRU (sesi yang
 * ditangani), Admin Dinas R (agregat status - tidak sensitif seperti
 * UmpanBalik, tidak perlu redaksi), Super Admin R (bypass).
 */
class RencanaTindakLanjutPolicy
{
    public function view(Pengguna $pengguna, RencanaTindakLanjut $rtl): bool
    {
        if (! $pengguna->can('sesi-supervisi.manage')) {
            return false;
        }

        $sesi = $rtl->sesi;

        return $sesi->guru_id === $pengguna->id
            || $sesi->supervisor_id === $pengguna->id
            || $pengguna->hasRole('admin_dinas')
            || ($pengguna->hasRole('kepala_sekolah') && $pengguna->sekolah_id === $sesi->sekolah_id);
    }

    /**
     * Dipanggil sebelum RTL ada (isiRtl pertama kali), menerima
     * SesiSupervisi, bukan RencanaTindakLanjut itu sendiri.
     */
    public function create(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        return $pengguna->can('sesi-supervisi.manage')
            && $pengguna->hasRole('supervisor')
            && $sesi->supervisor_id === $pengguna->id;
    }

    public function update(Pengguna $pengguna, RencanaTindakLanjut $rtl): bool
    {
        return $pengguna->can('sesi-supervisi.manage')
            && $pengguna->hasRole('supervisor')
            && $rtl->sesi->supervisor_id === $pengguna->id;
    }
}
