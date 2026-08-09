<?php

namespace App\Modules\Pelaporan\Queries;

use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use App\Modules\Analisis\Services\SkorCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Sprint 9 backlog (Modul 11): "LaporanAgregatQuery (terpisah dari
 * Service)" - pengecualian Repository Pattern untuk query agregat
 * kompleks (Dok 11 Bagian I.10), dipakai Dashboard Admin Dinas & Laporan
 * Sekolah. Hasil di-cache 15 menit via Cache::remember() dengan key
 * eksplisit per-sekolah supaya tidak bocor data lintas pengguna (Sprint 9
 * AC), memakai store default aplikasi (Redis - lihat CACHE_STORE di .env).
 *
 * Skor rata-rata memanggil ulang SkorCalculator (DRY, Bagian 4 Contributing
 * Guide - satu rumus/logika, satu lokasi) per sesi, bukan menyalin
 * rumusnya ke SQL agregat - dapat diterima untuk skala kabupaten (bukan
 * jutaan baris) dan sudah dilindungi cache TTL 15 menit.
 */
class LaporanAgregatQuery
{
    private const CACHE_TTL_MENIT = 15;

    public function __construct(private readonly SkorCalculator $skorCalculator) {}

    /**
     * @return array{
     *     sekolah_id: string,
     *     nama_sekolah: string,
     *     total_sesi: int,
     *     sesi_selesai: int,
     *     sesi_terobservasi: int,
     *     rata_rata_skor: float,
     *     tren_skor: array<int, array{sesi_id: string, tanggal: string, skor: float}>,
     *     rtl_tuntas_persen: float,
     * }
     */
    public function rekapSekolah(Sekolah $sekolah): array
    {
        return Cache::remember(
            "laporan:sekolah:{$sekolah->id}",
            now()->addMinutes(self::CACHE_TTL_MENIT),
            fn () => $this->hitungRekapSekolah($sekolah),
        );
    }

    /**
     * @return array{
     *     total_sekolah: int,
     *     total_sesi: int,
     *     sesi_selesai: int,
     *     rata_rata_skor_kabupaten: float,
     *     skor_per_sekolah: array<int, array{sekolah_id: string, nama_sekolah: string, rata_rata_skor: float, total_sesi: int}>,
     *     rtl_tertunda: int,
     * }
     */
    public function dashboardAgregat(): array
    {
        return Cache::remember(
            'laporan:dashboard:admin_dinas',
            now()->addMinutes(self::CACHE_TTL_MENIT),
            fn () => $this->hitungDashboardAgregat(),
        );
    }

    private function hitungRekapSekolah(Sekolah $sekolah): array
    {
        $sesiTerobservasi = $this->sesiTerobservasiUntukSekolah($sekolah->id);

        $trenSkor = [];
        $totalSkor = 0.0;

        foreach ($sesiTerobservasi as $sesi) {
            $skor = $this->skorCalculator->hitung($sesi)['total'];
            $trenSkor[] = [
                'sesi_id' => $sesi->id,
                'tanggal' => $sesi->tanggal->toDateString(),
                'skor' => $skor,
            ];
            $totalSkor += $skor;
        }

        $totalSesi = SesiSupervisi::where('sekolah_id', $sekolah->id)->count();
        $sesiSelesai = SesiSupervisi::where('sekolah_id', $sekolah->id)->where('status', 'selesai')->count();
        $sesiDenganRtl = SesiSupervisi::where('sekolah_id', $sekolah->id)->whereHas('rtl')->count();
        $rtlTuntas = SesiSupervisi::where('sekolah_id', $sekolah->id)->whereHas('rtl', fn ($q) => $q->where('status', 'selesai'))->count();

        return [
            'sekolah_id' => $sekolah->id,
            'nama_sekolah' => $sekolah->nama_sekolah,
            'total_sesi' => $totalSesi,
            'sesi_selesai' => $sesiSelesai,
            'sesi_terobservasi' => $sesiTerobservasi->count(),
            'rata_rata_skor' => $sesiTerobservasi->isNotEmpty() ? round($totalSkor / $sesiTerobservasi->count(), 2) : 0.0,
            'tren_skor' => $trenSkor,
            'rtl_tuntas_persen' => $sesiDenganRtl > 0 ? round(($rtlTuntas / $sesiDenganRtl) * 100, 1) : 0.0,
        ];
    }

    private function hitungDashboardAgregat(): array
    {
        $sekolahList = Sekolah::orderBy('nama_sekolah')->get();

        $rekapPerSekolah = $sekolahList->map(fn (Sekolah $sekolah) => $this->hitungRekapSekolah($sekolah))->values();

        $skorPerSekolah = $rekapPerSekolah->map(fn (array $rekap) => [
            'sekolah_id' => $rekap['sekolah_id'],
            'nama_sekolah' => $rekap['nama_sekolah'],
            'rata_rata_skor' => $rekap['rata_rata_skor'],
            'total_sesi' => $rekap['total_sesi'],
        ])->all();

        // Berbobot jumlah sesi TEROBSERVASI (bukan total_sesi) - sesi yang
        // belum diobservasi tidak boleh menyeret rata-rata kabupaten ke 0.
        $totalSesiTerobservasi = (int) $rekapPerSekolah->sum('sesi_terobservasi');
        $skorTerbobot = $rekapPerSekolah->sum(fn (array $r) => $r['rata_rata_skor'] * $r['sesi_terobservasi']);

        $rtlTertunda = SesiSupervisi::query()
            ->whereHas('umpanBalik')
            ->whereDoesntHave('rtl')
            ->count();

        return [
            'total_sekolah' => $sekolahList->count(),
            'total_sesi' => (int) $rekapPerSekolah->sum('total_sesi'),
            'sesi_selesai' => SesiSupervisi::where('status', 'selesai')->count(),
            'rata_rata_skor_kabupaten' => $totalSesiTerobservasi > 0 ? round($skorTerbobot / $totalSesiTerobservasi, 2) : 0.0,
            'skor_per_sekolah' => $skorPerSekolah,
            'rtl_tertunda' => $rtlTertunda,
        ];
    }

    /** @return Collection<int, SesiSupervisi> */
    private function sesiTerobservasiUntukSekolah(string $sekolahId): Collection
    {
        return SesiSupervisi::where('sekolah_id', $sekolahId)
            ->whereNotNull('instrumen_id')
            ->whereHas('hasilObservasi')
            ->with(['hasilObservasi.butir', 'instrumen'])
            ->orderBy('tanggal')
            ->get();
    }
}
