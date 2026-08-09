<?php

namespace Tests\Browser;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Sprint 11 backlog: "Dusk: siklus penuh Supervisor ... Sukses mobile &
 * desktop". Menguji jadwal -> pra-observasi -> observasi -> umpan balik ->
 * RTL -> selesai lewat browser sungguhan (bukan Livewire::test()), sesuai
 * milestone Sprint 8 ("siklus bisnis inti end-to-end dapat didemonstrasikan
 * penuh"). DatabaseMigrations (bukan RefreshDatabase) karena Dusk memukul
 * server aplikasi sungguhan lewat HTTP, bukan in-process test client -
 * transaksi RefreshDatabase tidak menjangkau proses server terpisah itu.
 */
class SupervisorSiklusPenuhTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_supervisor_menjalankan_siklus_penuh_hingga_selesai(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $sekolah = Sekolah::factory()->create();
        $guru = Pengguna::factory()->create(['sekolah_id' => $sekolah->id, 'nama' => 'Guru Dusk']);
        $guru->assignRole('guru');

        $supervisor = Pengguna::factory()->create([
            'email' => 'supervisor.dusk@sidoarjo.go.id',
            'password' => bcrypt('password-dusk'),
        ]);
        $supervisor->assignRole('supervisor');

        $instrumen = InstrumenObservasi::factory()->create(['terkunci' => true, 'skor_min' => 1, 'skor_maks' => 4]);
        ButirObservasi::factory()->create([
            'instrumen_id' => $instrumen->id,
            'bobot' => 100,
            'kode' => 'A1',
            'dimensi' => 'A. Pembukaan Pembelajaran',
        ]);

        $this->browse(function (Browser $browser) use ($supervisor, $guru) {
            $browser->visit('/login')
                ->type('#email', $supervisor->email)
                ->type('#password', 'password-dusk')
                ->press('Masuk')
                ->waitForLocation('/beranda');

            // 1. Buat Jadwal
            $browser->visit('/sesi-supervisi/buat-jadwal')
                ->select('#guru_id', $guru->id)
                ->select('#tipe_supervisor', 'internal')
                ->type('#tanggal', now()->addDay()->format('Y-m-d'))
                ->press('Simpan')
                ->waitForLocation('/sesi-supervisi')
                ->assertSee($guru->nama);
        });

        $sesi = SesiSupervisi::where('guru_id', $guru->id)->firstOrFail();
        $this->assertSame('dijadwalkan', $sesi->status);

        $this->browse(function (Browser $browser) use ($sesi) {
            // 2. Pra-Observasi
            $browser->visit("/sesi-supervisi/{$sesi->id}/pra-observasi")
                ->type('#fokus_observasi', 'Manajemen kelas dan keterlibatan siswa')
                ->press('Simpan')
                ->waitForLocation('/sesi-supervisi');
        });
        $this->assertSame('pra_observasi', $sesi->fresh()->status);

        $this->browse(function (Browser $browser) use ($sesi) {
            // 3. Observasi - radio skor bersifat sr-only (aksesibilitas), diisi
            // via JS langsung supaya lebih andal daripada klik pixel-perfect.
            $browser->visit("/sesi-supervisi/{$sesi->id}/observasi")
                ->waitFor('input[type=radio]')
                ->script("
                    const el = document.querySelector('input[type=radio][value=\"4\"]');
                    el.checked = true;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                ");
            $browser->pause(300)
                ->press('Selesai')
                ->acceptDialog()
                ->waitForLocation('/sesi-supervisi');
        });
        $this->assertSame('dianalisis', $sesi->fresh()->status);

        $this->browse(function (Browser $browser) use ($sesi) {
            // 4. Umpan Balik
            $browser->visit("/sesi-supervisi/{$sesi->id}/umpan-balik")
                ->type('#kekuatan', 'Penguasaan materi kuat.')
                ->type('#area_pengembangan', 'Manajemen waktu.')
                ->type('#rekomendasi', 'Gunakan pengatur waktu.')
                ->press('Simpan')
                ->waitForLocation('/sesi-supervisi');
        });
        $this->assertSame('umpan_balik', $sesi->fresh()->status);

        $this->browse(function (Browser $browser) use ($sesi) {
            // 5. RTL, lalu Tandai Selesai (BR-04) - tombol baru muncul setelah
            // RTL tersimpan, form tidak redirect (lihat FormRtl::simpan()).
            $browser->visit("/sesi-supervisi/{$sesi->id}/rtl")
                ->type('#deskripsi', 'Pelatihan manajemen waktu kelas.')
                ->type('#target_waktu', now()->addWeek()->format('Y-m-d'))
                ->select('#kategori', 'profesional')
                ->press('Simpan RTL')
                ->waitForText('Tandai Selesai')
                ->press('Tandai Selesai')
                ->acceptDialog()
                ->waitForLocation('/sesi-supervisi');
        });
        $this->assertSame('selesai', $sesi->fresh()->status);
    }
}
