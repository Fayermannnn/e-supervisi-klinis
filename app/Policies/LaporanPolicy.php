<?php

namespace App\Policies;

use App\Models\Pengguna;

/**
 * Sprint 9 (Modul 11): abilities tanpa objek Eloquent terikat (Dashboard
 * Agregat kabupaten-wide, drill-down individual), sehingga tidak lewat
 * auto-discovery Policy standar Laravel - didaftarkan manual sebagai Gate
 * di AppServiceProvider::boot(). Laporan Sekolah (per-objek Sekolah) tetap
 * di SekolahPolicy::viewLaporan().
 */
class LaporanPolicy
{
    public function viewDashboard(Pengguna $pengguna): bool
    {
        return $pengguna->can('laporan.manage') && $pengguna->hasAnyRole(['admin_dinas', 'super_admin']);
    }

    /**
     * BR-08: Akses UmpanBalik individual oleh Admin Dinas wajib justifikasi
     * tercatat. Drill-down hanya untuk Admin Dinas/Super Admin - Kepala
     * Sekolah tetap terbatas pada Laporan Sekolah agregat miliknya.
     */
    public function drillDownIndividual(Pengguna $pengguna): bool
    {
        return $pengguna->can('laporan.manage') && $pengguna->hasAnyRole(['admin_dinas', 'super_admin']);
    }
}
