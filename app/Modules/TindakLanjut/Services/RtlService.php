<?php

namespace App\Modules\TindakLanjut\Services;

use App\Exceptions\RtlNotFoundException;
use App\Exceptions\RtlRequiredBeforeCloseException;
use App\Exceptions\SesiSupervisiNotFoundException;
use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;

class RtlService
{
    /**
     * POST/PATCH keduanya lewat method ini (Sprint 8 AC) - berbeda dari
     * UmpanBalikService::isiUmpanBalik() yang create-once, RTL boleh
     * diperbarui (mis. deskripsi direvisi, status pelaksanaan diubah)
     * sebelum sesi ditutup.
     *
     * @param  array<string, mixed>  $data  deskripsi, target_waktu, kategori (opsional), status (opsional)
     *
     * @throws SesiSupervisiNotFoundException
     */
    public function isiRtl(string $sesiId, array $data): RencanaTindakLanjut
    {
        $sesi = SesiSupervisi::find($sesiId);

        if (! $sesi) {
            throw new SesiSupervisiNotFoundException;
        }

        $existing = RencanaTindakLanjut::where('sesi_id', $sesi->id)->first();

        $rtl = RencanaTindakLanjut::updateOrCreate(
            ['sesi_id' => $sesi->id],
            [
                'deskripsi' => $data['deskripsi'],
                'target_waktu' => $data['target_waktu'],
                'kategori' => $data['kategori'] ?? $existing?->kategori,
                'status' => $data['status'] ?? $existing?->status ?? 'belum',
                'tanggal_diisi' => $existing?->tanggal_diisi ?? now(),
            ],
        );

        if ($sesi->status === 'umpan_balik') {
            $sesi->update(['status' => 'rtl']);
        }

        return $rtl;
    }

    /**
     * BR-04: RTL wajib sebelum sesi ditandai selesai. Ini penegakan
     * langsung terhadap bottleneck B-04 - dicek murni di Service layer
     * (bukan DB trigger), sesuai keputusan arsitektur proyek untuk BR-04
     * secara eksplisit (kompleksitas trigger vs maintainability solo dev).
     *
     * @throws SesiSupervisiNotFoundException
     * @throws RtlRequiredBeforeCloseException
     */
    public function tandaiSelesai(string $sesiId): SesiSupervisi
    {
        $sesi = SesiSupervisi::find($sesiId);

        if (! $sesi) {
            throw new SesiSupervisiNotFoundException;
        }

        if (! $sesi->rtl()->exists()) {
            throw new RtlRequiredBeforeCloseException;
        }

        $sesi->update(['status' => 'selesai']);

        return $sesi;
    }

    /** @throws RtlNotFoundException */
    public function find(string $sesiId): RencanaTindakLanjut
    {
        $rtl = RencanaTindakLanjut::where('sesi_id', $sesiId)->first();

        if (! $rtl) {
            throw new RtlNotFoundException;
        }

        return $rtl;
    }
}
