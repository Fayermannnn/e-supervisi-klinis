<?php

namespace Tests\Feature\Auth;

use App\Modules\Auth\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class EnsureTwoFactorIsConfiguredTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_admin_dinas_without_two_factor_is_redirected_to_setup_from_beranda(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas', confirmTwoFactor: false), 'web');

        $this->get('/beranda')->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_dinas_without_two_factor_is_redirected_to_setup_from_sekolah(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas', confirmTwoFactor: false), 'web');

        $this->get('/sekolah')->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_dinas_can_still_reach_setup_screen_and_logout(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas', confirmTwoFactor: false), 'web');

        $this->get('/2fa/aktivasi')->assertOk();
        $this->post('/logout')->assertRedirect(route('login'));
    }

    public function test_admin_dinas_with_two_factor_active_is_not_redirected(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas');
        $service = app(TwoFactorService::class);
        $secret = $service->buatSecret();
        $service->aktifkan($pengguna, $secret, app(Google2FA::class)->getCurrentOtp($secret));

        $this->actingAs($pengguna->fresh(), 'web');

        $this->get('/beranda')->assertOk();
    }

    public function test_guru_is_never_redirected_to_setup(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $this->get('/beranda')->assertOk();
    }
}
