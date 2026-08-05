<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_sixth_failed_attempt_within_15_minutes_returns_429_on_api(): void
    {
        Pengguna::factory()->create([
            'email' => 'rate-api@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'rate-api@sidoarjo.go.id',
                'password' => 'salah',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rate-api@sidoarjo.go.id',
            'password' => 'salah',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('errors.0.code', 'AUTH_TOO_MANY_ATTEMPTS');
    }

    public function test_sixth_attempt_is_blocked_even_with_correct_password(): void
    {
        Pengguna::factory()->create([
            'email' => 'rate-correct@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'rate-correct@sidoarjo.go.id',
                'password' => 'salah',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rate-correct@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(429);
    }

    public function test_successful_login_clears_the_failed_attempt_counter(): void
    {
        Pengguna::factory()->create([
            'email' => 'rate-clear@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'rate-clear@sidoarjo.go.id',
                'password' => 'salah',
            ])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rate-clear@sidoarjo.go.id',
            'password' => 'rahasia123',
        ])->assertStatus(200);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rate-clear@sidoarjo.go.id',
            'password' => 'salah',
        ])->assertStatus(401);
    }

    public function test_sixth_failed_attempt_is_blocked_on_livewire_login_screen(): void
    {
        Pengguna::factory()->create([
            'email' => 'rate-livewire@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->set('email', 'rate-livewire@sidoarjo.go.id')
                ->set('password', 'salah')
                ->call('login')
                ->assertHasErrors('form');
        }

        Livewire::test(Login::class)
            ->set('email', 'rate-livewire@sidoarjo.go.id')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertHasErrors('form');

        $this->assertGuest();
    }
}
