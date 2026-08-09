<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;

/**
 * CRUD Matrix (Dok 04): Guru R (miliknya), Supervisor CRU (sesi yang
 * ditangani), Admin Dinas TIDAK punya akses langsung ke isi individual -
 * namun Sprint 7 (BR-08) mengizinkan Admin Dinas membuka endpoint dengan
 * field sensitif diredaksi ("redacted": true), bukan 403 diam-diam.
 * Redaksi aktual dilakukan di Controller, Policy ini hanya gerbang akses.
 */
class UmpanBalikPolicy
{
    public function view(Pengguna $pengguna, UmpanBalik $umpanBalik): bool
    {
        if (! $pengguna->can('sesi-supervisi.manage')) {
            return false;
        }

        $sesi = $umpanBalik->sesi;

        return $sesi->guru_id === $pengguna->id
            || $sesi->supervisor_id === $pengguna->id
            || $pengguna->hasRole('admin_dinas')
            || ($pengguna->hasRole('kepala_sekolah') && $pengguna->sekolah_id === $sesi->sekolah_id);
    }

    /**
     * Dipanggil sebelum UmpanBalik ada (isiUmpanBalik pertama kali),
     * sehingga menerima SesiSupervisi, bukan UmpanBalik itu sendiri.
     */
    public function create(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        return $pengguna->can('sesi-supervisi.manage')
            && $pengguna->hasRole('supervisor')
            && $sesi->supervisor_id === $pengguna->id;
    }

    public function update(Pengguna $pengguna, UmpanBalik $umpanBalik): bool
    {
        $sesi = $umpanBalik->sesi;

        return $pengguna->can('sesi-supervisi.manage')
            && $pengguna->hasRole('supervisor')
            && $sesi->supervisor_id === $pengguna->id;
    }

    public function refleksi(Pengguna $pengguna, UmpanBalik $umpanBalik): bool
    {
        $sesi = $umpanBalik->sesi;

        return $pengguna->can('sesi-supervisi.manage')
            && $pengguna->hasRole('guru')
            && $sesi->guru_id === $pengguna->id;
    }
}
