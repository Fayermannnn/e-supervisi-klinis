<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\RekomendasiPengembangan;
use App\Models\SesiSupervisi;

class RekomendasiPengembanganPolicy
{
    /**
     * Rekomendasi manual (POST /sesi-supervisi/{id}/rekomendasi) hanya
     * boleh dibuat oleh Supervisor untuk sesi yang ditanganinya sendiri.
     */
    public function create(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        return $pengguna->can('pengembangan.manage')
            && $pengguna->hasRole('supervisor')
            && $sesi->supervisor_id === $pengguna->id;
    }

    /**
     * Guru menandai status rekomendasi (belum/sedang/selesai) miliknya
     * sendiri saja.
     */
    public function ubahStatus(Pengguna $pengguna, RekomendasiPengembangan $rekomendasi): bool
    {
        return $pengguna->can('pengembangan.manage')
            && $rekomendasi->pengguna_id === $pengguna->id;
    }
}
