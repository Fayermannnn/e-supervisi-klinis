<?php

namespace App\Modules\Analisis\Services;

use App\Models\SesiSupervisi;

/**
 * Rumus berbobot (Sprint 6 backlog, DRY: dipanggil dari mana pun - endpoint
 * skor, Livewire Ringkasan Skor - bukan disalin).
 *
 * skor_tertimbang_butir = (skor / instrumen.skor_maks) x bobot_butir
 *
 * Bobot butir dalam persen (mis. 5.00 = 5%), dan seluruh bobot butir
 * berjumlah 100% di satu instrumen (Addendum 02, Bagian 2.1). skor
 * dipakai langsung sebagai pencapaian dari skor_maks - TIDAK digeser
 * relatif ke skor_min, karena "1" pada rubrik 4-level bukan berarti
 * pencapaian nol, melainkan level rubrik terendah. Total karenanya
 * berkisar 0-100 saat seluruh butir bernilai skor_min, hingga 100 penuh
 * saat seluruh butir bernilai skor_maks.
 */
class SkorCalculator
{
    /**
     * @return array{breakdown: array<int, array{butir_id: string, kode: string, dimensi: string, skor: int, bobot: float, skor_tertimbang: float}>, total: float}
     */
    public function hitung(SesiSupervisi $sesi): array
    {
        $instrumen = $sesi->instrumen;

        if (! $instrumen) {
            return ['breakdown' => [], 'total' => 0.0];
        }

        $hasilPerButir = $sesi->hasilObservasi()->with('butir')->get();

        $breakdown = [];
        $total = 0.0;

        foreach ($hasilPerButir as $hasil) {
            $butir = $hasil->butir;
            $bobot = (float) $butir->bobot;
            $skorTertimbang = round(($hasil->skor / $instrumen->skor_maks) * $bobot, 2);

            $breakdown[] = [
                'butir_id' => $butir->id,
                'kode' => $butir->kode,
                'dimensi' => $butir->dimensi,
                'skor' => $hasil->skor,
                'bobot' => $bobot,
                'skor_tertimbang' => $skorTertimbang,
            ];

            $total += $skorTertimbang;
        }

        return [
            'breakdown' => $breakdown,
            'total' => round($total, 2),
        ];
    }
}
