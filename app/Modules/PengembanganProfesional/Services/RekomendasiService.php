<?php

namespace App\Modules\PengembanganProfesional\Services;

use App\Exceptions\RekomendasiPengembanganNotFoundException;
use App\Models\HasilObservasi;
use App\Models\MateriPengembangan;
use App\Models\Pengguna;
use App\Models\RekomendasiPengembangan;
use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use App\Modules\Notifikasi\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RekomendasiService
{
    /**
     * Skor observasi ambang batas rendah (Addendum 02, BR-09b): butir
     * dengan skor <=2 memicu rekomendasi materi terkait.
     */
    private const AMBANG_SKOR_RENDAH = 2;

    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * BR-09a: kategori RTL memicu rekomendasi materi yang sesuai kategori
     * yang sama, dikirim ke guru pemilik sesi.
     */
    public function picuDariRtl(RencanaTindakLanjut $rtl): Collection
    {
        $sesi = $rtl->sesi;
        $materiSesuai = MateriPengembangan::where('kategori', $rtl->kategori)->get();

        return $materiSesuai->map(
            fn (MateriPengembangan $materi) => $this->buatJikaBelumAda($sesi->guru, $sesi, $materi, 'otomatis_rtl'),
        )->filter();
    }

    /**
     * BR-09b (Addendum 02, Dual Trigger): skor butir observasi <=2 pada
     * hasil final memicu rekomendasi lewat pemetaan butir->materi
     * (butir_observasi_materi). Independen dari BR-09a, boleh
     * menghasilkan rekomendasi ganda untuk sesi & guru yang sama - tidak
     * dideduplikasi otomatis lintas sumber (dicatat sebagai catatan
     * implementasi Addendum 02, bukan business rule tambahan).
     */
    public function picuDariObservasi(SesiSupervisi $sesi): Collection
    {
        $hasilRendah = HasilObservasi::where('sesi_id', $sesi->id)
            ->where('skor', '<=', self::AMBANG_SKOR_RENDAH)
            ->with('butir.materiPengembangan')
            ->get();

        $dibuat = collect();

        foreach ($hasilRendah as $hasil) {
            foreach ($hasil->butir->materiPengembangan as $materi) {
                $dibuat->push($this->buatJikaBelumAda($sesi->guru, $sesi, $materi, 'otomatis_observasi'));
            }
        }

        return $dibuat->filter();
    }

    /**
     * Mencegah duplikasi saat trigger yang SAMA berjalan berulang (mis.
     * RTL diedit beberapa kali) - bukan deduplikasi ANTAR sumber
     * (otomatis_rtl vs otomatis_observasi), yang secara sengaja tetap
     * independen sesuai Addendum 02.
     */
    private function buatJikaBelumAda(Pengguna $guru, SesiSupervisi $sesi, MateriPengembangan $materi, string $sumber): ?RekomendasiPengembangan
    {
        $sudahAda = RekomendasiPengembangan::where('pengguna_id', $guru->id)
            ->where('sesi_id', $sesi->id)
            ->where('materi_id', $materi->id)
            ->where('sumber', $sumber)
            ->exists();

        if ($sudahAda) {
            return null;
        }

        $rekomendasi = RekomendasiPengembangan::create([
            'pengguna_id' => $guru->id,
            'sesi_id' => $sesi->id,
            'materi_id' => $materi->id,
            'sumber' => $sumber,
            'status' => 'belum',
        ]);

        $this->notificationService->kirim(
            penerima: $guru,
            judul: 'Rekomendasi Materi Pengembangan Baru',
            pesan: "Materi \"{$materi->judul}\" direkomendasikan untuk Anda.",
        );

        return $rekomendasi;
    }

    /** @param  array<string, mixed>  $data */
    public function rekomendasikanManual(array $data): RekomendasiPengembangan
    {
        $rekomendasi = RekomendasiPengembangan::create([
            'pengguna_id' => $data['pengguna_id'],
            'sesi_id' => $data['sesi_id'] ?? null,
            'materi_id' => $data['materi_id'],
            'sumber' => 'manual',
            'status' => 'belum',
        ]);

        $materi = $rekomendasi->materi;
        $this->notificationService->kirim(
            penerima: $rekomendasi->pengguna,
            judul: 'Rekomendasi Materi Pengembangan Baru',
            pesan: "Materi \"{$materi->judul}\" direkomendasikan untuk Anda.",
        );

        return $rekomendasi;
    }

    public function untukPengguna(string $penggunaId, int $perPage = 15): LengthAwarePaginator
    {
        return RekomendasiPengembangan::where('pengguna_id', $penggunaId)
            ->with('materi')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * US: Guru menandai status rekomendasi (belum/sedang/selesai).
     *
     * @throws RekomendasiPengembanganNotFoundException
     */
    public function ubahStatus(string $id, string $status): RekomendasiPengembangan
    {
        $rekomendasi = $this->find($id);
        $rekomendasi->update(['status' => $status]);

        return $rekomendasi;
    }

    /** @throws RekomendasiPengembanganNotFoundException */
    public function find(string $id): RekomendasiPengembangan
    {
        $rekomendasi = RekomendasiPengembangan::find($id);

        if (! $rekomendasi) {
            throw new RekomendasiPengembanganNotFoundException;
        }

        return $rekomendasi;
    }
}
