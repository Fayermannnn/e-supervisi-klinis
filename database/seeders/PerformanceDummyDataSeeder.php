<?php

namespace Database\Seeders;

use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\Pengguna;
use App\Models\RencanaTindakLanjut;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Database\Seeder;

/**
 * Sprint 9 backlog: "Seeder data dummy volume besar ... Siap untuk
 * Performance Test". TIDAK didaftarkan di DatabaseSeeder::run() -
 * dijalankan manual (php artisan db:seed --class=PerformanceDummyDataSeeder)
 * hanya saat dibutuhkan uji performa Dashboard Agregat/Laporan Sekolah,
 * supaya migrate --seed rutin tetap cepat.
 */
class PerformanceDummyDataSeeder extends Seeder
{
    private const JUMLAH_SEKOLAH = 30;

    private const SESI_PER_SEKOLAH = 20;

    private const STATUS_SIKLUS = ['draft', 'dijadwalkan', 'pra_observasi', 'observasi', 'dianalisis', 'umpan_balik', 'rtl', 'selesai'];

    private const STATUS_DENGAN_HASIL_OBSERVASI = ['dianalisis', 'umpan_balik', 'rtl', 'selesai'];

    private const STATUS_DENGAN_UMPAN_BALIK = ['umpan_balik', 'rtl', 'selesai'];

    private const STATUS_DENGAN_RTL = ['rtl', 'selesai'];

    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(InstrumenSeeder::class);

        $instrumen = InstrumenObservasi::where('versi', 1)->firstOrFail();
        $butirList = $instrumen->butir()->get();

        $totalSesi = 0;

        Sekolah::factory()->count(self::JUMLAH_SEKOLAH)->create()->each(function (Sekolah $sekolah) use ($instrumen, $butirList, &$totalSesi) {
            $guruList = Pengguna::factory()->count(5)->create(['sekolah_id' => $sekolah->id]);
            $guruList->each(fn (Pengguna $guru) => $guru->assignRole('guru'));

            $supervisor = Pengguna::factory()->create();
            $supervisor->assignRole('supervisor');

            for ($i = 0; $i < self::SESI_PER_SEKOLAH; $i++) {
                $status = fake()->randomElement(self::STATUS_SIKLUS);
                $adaHasilObservasi = in_array($status, self::STATUS_DENGAN_HASIL_OBSERVASI, true);

                $sesi = SesiSupervisi::factory()->create([
                    'sekolah_id' => $sekolah->id,
                    'guru_id' => $guruList->random()->id,
                    'supervisor_id' => $supervisor->id,
                    'status' => $status,
                    'instrumen_id' => $adaHasilObservasi ? $instrumen->id : null,
                    'tanggal' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                ]);

                if ($adaHasilObservasi) {
                    foreach ($butirList as $butir) {
                        HasilObservasi::factory()->create([
                            'sesi_id' => $sesi->id,
                            'butir_id' => $butir->id,
                        ]);
                    }
                }

                if (in_array($status, self::STATUS_DENGAN_UMPAN_BALIK, true)) {
                    UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);
                }

                if (in_array($status, self::STATUS_DENGAN_RTL, true)) {
                    RencanaTindakLanjut::factory()->create([
                        'sesi_id' => $sesi->id,
                        'status' => $status === 'selesai' ? 'selesai' : fake()->randomElement(['belum', 'sedang', 'selesai']),
                    ]);
                }

                $totalSesi++;
            }
        });

        $jumlahSekolah = self::JUMLAH_SEKOLAH;
        $this->command?->info("Data dummy volume besar selesai: {$jumlahSekolah} sekolah, {$totalSesi} sesi supervisi.");
    }
}
