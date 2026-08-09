<?php

namespace Tests\Browser;

use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PragmaRX\Google2FA\Google2FA;
use Tests\DuskTestCase;

/**
 * Sprint 11 backlog: "Dusk: drill-down Admin Dinas berjustifikasi ... Sesuai
 * TC-PELAPORAN-001". BR-08 diuji lewat browser sungguhan termasuk alur 2FA
 * wajib admin_dinas (Sprint 2), bukan Sanctum::actingAs() seperti
 * LaporanControllerTest - di sinilah drill-down benar-benar diklik lewat UI
 * (DrillDownIndividual, ditambahkan Sprint 11 untuk melengkapi endpoint
 * Sprint 9 yang sebelumnya tidak punya layar).
 */
class AdminDinasDrillDownTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_dinas_melihat_detail_individual_setelah_mengisi_justifikasi(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $admin = Pengguna::factory()->create([
            'email' => 'admin.dusk@sidoarjo.go.id',
            'password' => bcrypt('password-dusk'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('admin_dinas');

        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create([
            'sesi_id' => $sesi->id,
            'kekuatan' => 'Penguasaan kelas sangat baik.',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $sesi, $google2fa, $secret) {
            $browser->visit('/login')
                ->type('#email', $admin->email)
                ->type('#password', 'password-dusk')
                ->press('Masuk')
                ->waitForText('Kode Verifikasi')
                ->type('#twoFactorCode', $google2fa->getCurrentOtp($secret))
                ->press('Verifikasi')
                ->waitForLocation('/beranda');

            $browser->visit("/laporan/individual/{$sesi->id}")
                ->assertSee('Justifikasi')
                ->type('#justifikasi', 'Audit rutin triwulan atas permintaan Kepala Dinas.')
                ->press('Lihat Detail')
                ->waitForTextIn('[data-testid="hasil-drill-down"]', 'Penguasaan kelas sangat baik.')
                ->assertSee('tercatat di Log Audit');
        });

        $this->assertDatabaseHas('audit_log', [
            'pengguna_id' => $admin->id,
            'aksi' => 'LAPORAN_DRILL_DOWN_INDIVIDUAL',
        ]);
    }
}
