<?php

namespace App\Modules\Pelaporan\Services;

use App\Exceptions\SesiSupervisiNotFoundException;
use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use App\Modules\Analisis\Services\SkorCalculator;
use App\Modules\AuditLog\Services\AuditLogService;

/**
 * Sprint 9 (Modul 11): implementasi penuh BR-08 ("Akses UmpanBalik
 * individual oleh Admin Dinas wajib justifikasi tercatat"). Sprint 7 hanya
 * menegakkan separuh BR-08 (redaksi field sensitif di
 * UmpanBalikController::show()) - drill-down sungguhan (justifikasi wajib
 * + audit trail) baru ada di sini, sesuai pembagian scope pada Product
 * Backlog baseline (Bagian II, Sprint 7 vs Sprint 9).
 */
class PelaporanService
{
    public function __construct(
        private readonly SkorCalculator $skorCalculator,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array{
     *     sesi_id: string,
     *     guru: string,
     *     supervisor: string,
     *     sekolah: string,
     *     status: string,
     *     skor: array{breakdown: array, total: float},
     *     umpan_balik: array{kekuatan: ?string, area_pengembangan: ?string, rekomendasi: ?string, refleksi_guru: ?string}|null,
     *     rtl: array{deskripsi: ?string, target_waktu: ?string, status: ?string}|null,
     * }
     *
     * @throws SesiSupervisiNotFoundException
     */
    public function drillDownIndividual(Pengguna $admin, string $sesiId, string $justifikasi, ?string $ipAddress, ?string $correlationId): array
    {
        $sesi = SesiSupervisi::with(['guru', 'supervisor', 'sekolah', 'umpanBalik', 'rtl'])->find($sesiId);

        if (! $sesi) {
            throw new SesiSupervisiNotFoundException;
        }

        $this->auditLogService->catat(
            pengguna: $admin,
            aksi: 'LAPORAN_DRILL_DOWN_INDIVIDUAL',
            deskripsi: "Justifikasi: {$justifikasi}",
            ipAddress: $ipAddress,
            correlationId: $correlationId,
            modelType: SesiSupervisi::class,
            modelId: $sesi->id,
        );

        return [
            'sesi_id' => $sesi->id,
            'guru' => $sesi->guru?->nama ?? '',
            'supervisor' => $sesi->supervisor?->nama ?? '',
            'sekolah' => $sesi->sekolah?->nama_sekolah ?? '',
            'status' => $sesi->status,
            'skor' => $this->skorCalculator->hitung($sesi),
            'umpan_balik' => $sesi->umpanBalik ? [
                'kekuatan' => $sesi->umpanBalik->kekuatan,
                'area_pengembangan' => $sesi->umpanBalik->area_pengembangan,
                'rekomendasi' => $sesi->umpanBalik->rekomendasi,
                'refleksi_guru' => $sesi->umpanBalik->refleksi_guru,
            ] : null,
            'rtl' => $sesi->rtl ? [
                'deskripsi' => $sesi->rtl->deskripsi,
                'target_waktu' => $sesi->rtl->target_waktu?->toDateString(),
                'status' => $sesi->rtl->status,
            ] : null,
        ];
    }
}
