<?php

namespace Database\Seeders;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use Illuminate\Database\Seeder;

/**
 * Addendum 02 (5 Agustus 2026) menutup PENDING #2/#3 dan menetapkan struktur
 * FINAL: skala 1-4, 10 dimensi, 20 butir (2 sub-indikator/dimensi), bobot
 * dimensi 8-12%, bobot butir 4-7%. Namun teks butir & definisi operasional
 * yang SEBENARNYA berasal dari "Lampiran_Instrumen_Supervisi_Klinis.docx",
 * yang tidak ditemukan di disk saat seeder ini ditulis - lihat catatan di
 * PROJECT CONTINUITY HANDOFF. Seeder ini HANYA mengisi struktur (dimensi,
 * kode, bobot) dengan teks PLACEHOLDER yang jelas ditandai, BUKAN konten
 * tervalidasi. terkunci sengaja dibiarkan false supaya Admin Dinas bisa
 * mengoreksi bebas begitu file sumber ditemukan - jangan aktifkan versi ini
 * sampai teks placeholder diganti dengan konten asli.
 */
class InstrumenSeeder extends Seeder
{
    private const DIMENSI = [
        'A' => 'A. Pembukaan Pembelajaran',
        'B' => 'B. Penguasaan Materi',
        'C' => 'C. Strategi Pembelajaran',
        'D' => 'D. Aktivitas Peserta Didik',
        'E' => 'E. Diferensiasi Pembelajaran',
        'F' => 'F. Penggunaan Media Pembelajaran',
        'G' => 'G. Komunikasi Guru',
        'H' => 'H. Pengelolaan Kelas',
        'I' => 'I. Asesmen Formatif',
        'J' => 'J. Penutup Pembelajaran',
    ];

    public function run(): void
    {
        $instrumen = InstrumenObservasi::firstOrCreate(
            ['versi' => 1],
            ['nama' => 'Instrumen Observasi Kelas (Draf Awal - Addendum 02)', 'skor_min' => 1, 'skor_maks' => 4, 'terkunci' => false],
        );

        if ($instrumen->butir()->exists()) {
            return;
        }

        foreach (self::DIMENSI as $huruf => $namaDimensi) {
            foreach ([1, 2] as $subIndikator) {
                ButirObservasi::create([
                    'instrumen_id' => $instrumen->id,
                    'kode' => "{$huruf}{$subIndikator}",
                    'dimensi' => $namaDimensi,
                    'teks' => "[PLACEHOLDER - belum tersedia] Sub-indikator {$huruf}{$subIndikator} pada dimensi \"{$namaDimensi}\".",
                    'definisi_operasional' => '[PLACEHOLDER] Definisi operasional dan rubrik 4-level menunggu isi asli dari Lampiran_Instrumen_Supervisi_Klinis.docx (Addendum 02, Bagian 2.1) - berkas ini belum ditemukan di repositori.',
                    'bobot' => 5.00,
                ]);
            }
        }
    }
}
