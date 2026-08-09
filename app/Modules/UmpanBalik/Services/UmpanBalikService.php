<?php

namespace App\Modules\UmpanBalik\Services;

use App\Exceptions\SesiSupervisiNotFoundException;
use App\Exceptions\UmpanBalikNotFoundException;
use App\Exceptions\UmpanBalikSudahAdaException;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;

class UmpanBalikService
{
    /**
     * BR-03: Umpan balik maksimal 7 hari pasca-observasi - dicatat lewat
     * flag `terlambat`, TIDAK memblokir pengisian bila lewat tenggat.
     * Diukur dari sesi.tanggal (tanggal observasi berlangsung) karena
     * sesi_supervisi tidak menyimpan timestamp terpisah kapan status
     * berpindah ke "dianalisis".
     *
     * @param  array<string, mixed>  $data  kekuatan, area_pengembangan, rekomendasi, pendekatan_dipakai (opsional)
     *
     * @throws SesiSupervisiNotFoundException
     * @throws UmpanBalikSudahAdaException
     */
    public function isiUmpanBalik(string $sesiId, array $data): UmpanBalik
    {
        $sesi = SesiSupervisi::find($sesiId);

        if (! $sesi) {
            throw new SesiSupervisiNotFoundException;
        }

        if ($sesi->umpanBalik()->exists()) {
            throw new UmpanBalikSudahAdaException;
        }

        $sekarang = now();
        $terlambat = $sekarang->diffInDays($sesi->tanggal, absolute: true) > 7;

        $umpanBalik = UmpanBalik::create([
            'sesi_id' => $sesi->id,
            'kekuatan' => $data['kekuatan'],
            'area_pengembangan' => $data['area_pengembangan'],
            'rekomendasi' => $data['rekomendasi'],
            'pendekatan_dipakai' => $data['pendekatan_dipakai'] ?? null,
            'terlambat' => $terlambat,
            'tanggal_diisi' => $sekarang,
        ]);

        $sesi->update(['status' => 'umpan_balik']);

        return $umpanBalik;
    }

    /**
     * BR-10: pendekatan_dipakai boleh berbeda dari pendekatan_disarankan -
     * dicatat, tidak diblokir (supervisor tetap dipersilakan menyimpang
     * dari saran sistem berdasarkan penilaian profesionalnya).
     *
     * @throws UmpanBalikNotFoundException
     */
    public function ubahPendekatan(string $sesiId, string $pendekatanDipakai): UmpanBalik
    {
        $umpanBalik = $this->find($sesiId);

        $umpanBalik->update(['pendekatan_dipakai' => $pendekatanDipakai]);

        return $umpanBalik;
    }

    /**
     * US-06: Guru mengisi refleksi atas umpan balik yang diterimanya.
     *
     * @throws UmpanBalikNotFoundException
     */
    public function isiRefleksi(string $sesiId, string $refleksi): UmpanBalik
    {
        $umpanBalik = $this->find($sesiId);

        $umpanBalik->update(['refleksi_guru' => $refleksi]);

        return $umpanBalik;
    }

    /** @throws UmpanBalikNotFoundException */
    public function find(string $sesiId): UmpanBalik
    {
        $umpanBalik = UmpanBalik::where('sesi_id', $sesiId)->first();

        if (! $umpanBalik) {
            throw new UmpanBalikNotFoundException;
        }

        return $umpanBalik;
    }
}
