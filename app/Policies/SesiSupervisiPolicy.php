<?php

namespace App\Policies;

use App\Models\Pengguna;
use App\Models\SesiSupervisi;

class SesiSupervisiPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('sesi-supervisi.manage');
    }

    /**
     * Guru hanya melihat sesinya sendiri; Supervisor hanya sesi yang
     * ditanganinya; Kepala Sekolah melihat seluruh sesi di sekolahnya
     * (read-only, tidak mendapat method create/update di bawah).
     */
    public function view(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        if (! $pengguna->can('sesi-supervisi.manage')) {
            return false;
        }

        return $sesi->guru_id === $pengguna->id
            || $sesi->supervisor_id === $pengguna->id
            || $this->kepalaSekolahDiSekolahYangSama($pengguna, $sesi);
    }

    /**
     * Hanya Supervisor yang boleh membuat jadwal (CRUD Matrix: Guru hanya R
     * atas SesiSupervisi).
     */
    public function create(Pengguna $pengguna): bool
    {
        return $pengguna->can('sesi-supervisi.manage') && $pengguna->hasRole('supervisor');
    }

    /**
     * Hanya Supervisor yang menangani sesi tsb yang boleh mengubahnya
     * (mis. isiPraObservasi()).
     */
    public function update(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        return $pengguna->can('sesi-supervisi.manage')
            && $pengguna->hasRole('supervisor')
            && $sesi->supervisor_id === $pengguna->id;
    }

    public function delete(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        return false;
    }

    private function kepalaSekolahDiSekolahYangSama(Pengguna $pengguna, SesiSupervisi $sesi): bool
    {
        return $pengguna->hasRole('kepala_sekolah')
            && $pengguna->sekolah_id !== null
            && $pengguna->sekolah_id === $sesi->sekolah_id;
    }
}
