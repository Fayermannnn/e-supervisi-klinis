<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk()->assertSeeLivewire(Login::class);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $pengguna = Pengguna::factory()->create([
            'email' => 'guru@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'guru@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertRedirect(route('beranda'));

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        Pengguna::factory()->create([
            'email' => 'guru@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'guru@sidoarjo.go.id')
            ->set('password', 'salah')
            ->call('login')
            ->assertHasErrors('form');

        $this->assertGuest();
    }

    public function test_email_and_password_are_required(): void
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email', 'password']);
    }

    public function test_guest_route_redirects_authenticated_user_away_from_login(): void
    {
        $pengguna = Pengguna::factory()->create();

        $this->actingAs($pengguna, 'web')
            ->get('/login')
            ->assertRedirect('/');
    }

    public function test_beranda_requires_authentication(): void
    {
        $this->get('/beranda')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_logout(): void
    {
        $pengguna = Pengguna::factory()->create();

        $this->actingAs($pengguna, 'web')
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
