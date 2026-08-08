<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class TwoFactorLoginTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guru_login_does_not_require_two_factor_code(): void
    {
        $pengguna = $this->penggunaWithRole('guru', [
            'email' => 'guru@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'guru@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.pengguna.id', $pengguna->id);
    }

    public function test_admin_dinas_without_two_factor_configured_can_login_without_code(): void
    {
        $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ], confirmTwoFactor: false);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_dinas_with_two_factor_configured_requires_code(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $this->aktifkanTwoFactor($pengguna);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.code', 'AUTH_TWO_FACTOR_REQUIRED');
    }

    public function test_admin_dinas_with_wrong_two_factor_code_is_rejected(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $this->aktifkanTwoFactor($pengguna);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => 'rahasia123',
            'two_factor_code' => '000000',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('errors.0.code', 'AUTH_INVALID_TWO_FACTOR_CODE');
    }

    public function test_admin_dinas_with_correct_two_factor_code_logs_in(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $secret = $this->aktifkanTwoFactor($pengguna);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => 'rahasia123',
            'two_factor_code' => app(Google2FA::class)->getCurrentOtp($secret),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.pengguna.id', $pengguna->id);
    }

    public function test_admin_dinas_can_login_with_recovery_code(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $this->aktifkanTwoFactor($pengguna);

        $kodePemulihan = $pengguna->fresh()->two_factor_recovery_codes[0];

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => 'rahasia123',
            'two_factor_code' => $kodePemulihan,
        ]);

        $response->assertStatus(200);

        // Kode pemulihan sekali pakai: percobaan kedua ditolak.
        $response2 = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => 'rahasia123',
            'two_factor_code' => $kodePemulihan,
        ]);

        $response2->assertStatus(401)
            ->assertJsonPath('errors.0.code', 'AUTH_INVALID_TWO_FACTOR_CODE');
    }

    private function aktifkanTwoFactor(Pengguna $pengguna): string
    {
        $service = app(TwoFactorService::class);
        $secret = $service->buatSecret();
        $kodeValid = app(Google2FA::class)->getCurrentOtp($secret);
        $service->aktifkan($pengguna, $secret, $kodeValid);

        return $secret;
    }
}
