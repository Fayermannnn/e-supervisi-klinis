<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\Pengguna;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class TwoFactorLoginScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_correct_password_alone_does_not_log_in_when_two_factor_is_active(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $this->aktifkanTwoFactor($pengguna);

        Livewire::test(Login::class)
            ->set('email', 'admin@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertSet('butuhTwoFactor', true)
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_wrong_two_factor_code_is_rejected(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $this->aktifkanTwoFactor($pengguna);

        Livewire::test(Login::class)
            ->set('email', 'admin@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('login')
            ->set('twoFactorCode', '000000')
            ->call('login')
            ->assertHasErrors('twoFactorCode');

        $this->assertGuest();
    }

    public function test_correct_two_factor_code_completes_login(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', [
            'email' => 'admin@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);
        $secret = $this->aktifkanTwoFactor($pengguna);

        Livewire::test(Login::class)
            ->set('email', 'admin@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('login')
            ->set('twoFactorCode', app(Google2FA::class)->getCurrentOtp($secret))
            ->call('login')
            ->assertRedirect(route('beranda'));

        $this->assertAuthenticatedAs($pengguna->fresh());
    }

    public function test_kepala_sekolah_login_skips_two_factor_step(): void
    {
        $this->penggunaWithRole('kepala_sekolah', [
            'email' => 'kepsek@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'kepsek@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertRedirect(route('beranda'));
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
