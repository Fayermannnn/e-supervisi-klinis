<?php

namespace Tests\Feature\Auth;

use App\Exceptions\AuthInvalidTwoFactorCodeException;
use App\Models\Pengguna;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class TwoFactorServiceTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_wajib2fa_is_true_only_for_admin_dinas_and_super_admin(): void
    {
        $service = app(TwoFactorService::class);

        $this->assertTrue($service->wajib2fa($this->penggunaWithRole('admin_dinas')));
        $this->assertTrue($service->wajib2fa($this->penggunaWithRole('super_admin')));
        $this->assertFalse($service->wajib2fa($this->penggunaWithRole('kepala_sekolah')));
        $this->assertFalse($service->wajib2fa($this->penggunaWithRole('guru')));
        $this->assertFalse($service->wajib2fa($this->penggunaWithRole('supervisor')));
    }

    public function test_sudah_aktif_reflects_confirmed_at_column(): void
    {
        $service = app(TwoFactorService::class);
        $pengguna = Pengguna::factory()->create();

        $this->assertFalse($service->sudahAktif($pengguna));

        $pengguna->forceFill(['two_factor_confirmed_at' => now()])->save();

        $this->assertTrue($service->sudahAktif($pengguna->fresh()));
    }

    public function test_aktifkan_rejects_wrong_confirmation_code(): void
    {
        $service = app(TwoFactorService::class);
        $pengguna = Pengguna::factory()->create();
        $secret = $service->buatSecret();

        $this->expectException(AuthInvalidTwoFactorCodeException::class);

        $service->aktifkan($pengguna, $secret, '000000');
    }

    public function test_aktifkan_stores_encrypted_secret_and_returns_recovery_codes(): void
    {
        $service = app(TwoFactorService::class);
        $pengguna = Pengguna::factory()->create();
        $secret = $service->buatSecret();
        $kodeValid = app(Google2FA::class)->getCurrentOtp($secret);

        $kodePemulihan = $service->aktifkan($pengguna, $secret, $kodeValid);

        $this->assertCount(8, $kodePemulihan);
        $this->assertTrue($service->sudahAktif($pengguna->fresh()));

        $raw = DB::table('pengguna')->where('id', $pengguna->id)->value('two_factor_secret');
        $this->assertNotSame($secret, $raw, 'Secret must be encrypted at rest, not stored as plain text.');
    }

    public function test_verifikasi_login_accepts_valid_totp_code(): void
    {
        $service = app(TwoFactorService::class);
        $pengguna = Pengguna::factory()->create();
        $secret = $service->buatSecret();
        $kodeValid = app(Google2FA::class)->getCurrentOtp($secret);
        $service->aktifkan($pengguna, $secret, $kodeValid);

        $kodeLogin = app(Google2FA::class)->getCurrentOtp($secret);

        $this->assertTrue($service->verifikasiLogin($pengguna->fresh(), $kodeLogin));
    }

    public function test_verifikasi_login_accepts_recovery_code_once(): void
    {
        $service = app(TwoFactorService::class);
        $pengguna = Pengguna::factory()->create();
        $secret = $service->buatSecret();
        $kodeValid = app(Google2FA::class)->getCurrentOtp($secret);
        $kodePemulihan = $service->aktifkan($pengguna, $secret, $kodeValid);

        $pengguna = $pengguna->fresh();
        $kode = $kodePemulihan[0];

        $this->assertTrue($service->verifikasiLogin($pengguna, $kode));
        $this->assertFalse($service->verifikasiLogin($pengguna->fresh(), $kode));
    }

    public function test_verifikasi_login_rejects_invalid_code(): void
    {
        $service = app(TwoFactorService::class);
        $pengguna = Pengguna::factory()->create();
        $secret = $service->buatSecret();
        $kodeValid = app(Google2FA::class)->getCurrentOtp($secret);
        $service->aktifkan($pengguna, $secret, $kodeValid);

        $this->assertFalse($service->verifikasiLogin($pengguna->fresh(), '000000'));
    }
}
