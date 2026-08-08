<?php

namespace App\Policies;

use App\Models\Pengguna;

class PenggunaPolicy
{
    public function viewAny(Pengguna $pengguna): bool
    {
        return $pengguna->can('pengguna.manage');
    }

    public function view(Pengguna $pengguna, Pengguna $target): bool
    {
        return $pengguna->can('pengguna.manage') && $this->sekolahDiperbolehkan($pengguna, $target->sekolah_id);
    }

    public function create(Pengguna $pengguna, ?string $sekolahId): bool
    {
        return $pengguna->can('pengguna.manage') && $this->sekolahDiperbolehkan($pengguna, $sekolahId);
    }

    public function update(Pengguna $pengguna, Pengguna $target): bool
    {
        return $pengguna->can('pengguna.manage') && $this->sekolahDiperbolehkan($pengguna, $target->sekolah_id);
    }

    public function delete(Pengguna $pengguna, Pengguna $target): bool
    {
        return $pengguna->can('pengguna.manage') && $this->sekolahDiperbolehkan($pengguna, $target->sekolah_id);
    }

    /**
     * Admin Dinas mengelola pengguna se-kabupaten; Kepala Sekolah hanya boleh
     * mengelola pengguna di sekolahnya sendiri (data terisolasi per sekolah).
     */
    private function sekolahDiperbolehkan(Pengguna $pengguna, ?string $sekolahId): bool
    {
        if ($pengguna->hasRole('admin_dinas')) {
            return true;
        }

        return $pengguna->hasRole('kepala_sekolah')
            && $pengguna->sekolah_id !== null
            && $pengguna->sekolah_id === $sekolahId;
    }
}
