<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\SetupTwoFactor;
use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class SetupTwoFactorScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guru_cannot_access_setup_screen(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $this->get('/2fa/aktivasi')->assertForbidden();
    }

    public function test_admin_dinas_sees_qr_code_and_secret_when_not_yet_configured(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas', confirmTwoFactor: false), 'web');

        Livewire::test(SetupTwoFactor::class)
            ->assertSet('sudahDikonfirmasi', false)
            ->assertSee('svg', false);
    }

    public function test_wrong_confirmation_code_is_rejected(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas', confirmTwoFactor: false), 'web');

        Livewire::test(SetupTwoFactor::class)
            ->set('kodeKonfirmasi', '000000')
            ->call('konfirmasi')
            ->assertHasErrors('kodeKonfirmasi')
            ->assertSet('sudahDikonfirmasi', false);
    }

    public function test_correct_confirmation_code_activates_and_shows_recovery_codes(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas', confirmTwoFactor: false);
        $this->actingAs($pengguna, 'web');

        $component = Livewire::test(SetupTwoFactor::class);
        $secret = $component->get('secret');

        $component->set('kodeKonfirmasi', app(Google2FA::class)->getCurrentOtp($secret))
            ->call('konfirmasi')
            ->assertSet('sudahDikonfirmasi', true)
            ->assertHasNoErrors();

        $this->assertCount(8, $component->get('kodePemulihan'));
        $this->assertTrue(app(TwoFactorService::class)->sudahAktif($pengguna->fresh()));
    }

    public function test_already_active_admin_sees_status_message_without_new_codes(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas');
        $service = app(TwoFactorService::class);
        $secret = $service->buatSecret();
        $service->aktifkan($pengguna, $secret, app(Google2FA::class)->getCurrentOtp($secret));

        $this->actingAs($pengguna->fresh(), 'web');

        Livewire::test(SetupTwoFactor::class)
            ->assertSet('sudahDikonfirmasi', true)
            ->assertSet('kodePemulihan', []);
    }
}
