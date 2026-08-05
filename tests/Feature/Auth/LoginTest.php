<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_issues_a_token(): void
    {
        $pengguna = Pengguna::factory()->create([
            'email' => 'guru@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'guru@sidoarjo.go.id',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('errors', null)
            ->assertJsonPath('data.pengguna.id', $pengguna->id)
            ->assertJsonStructure([
                'data' => ['token', 'pengguna' => ['id', 'nama', 'email']],
                'meta',
                'errors',
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_with_wrong_password_returns_envelope_error(): void
    {
        Pengguna::factory()->create([
            'email' => 'guru@sidoarjo.go.id',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'guru@sidoarjo.go.id',
            'password' => 'salah',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.0.code', 'AUTH_INVALID_CREDENTIALS');
    }

    public function test_login_with_unknown_email_returns_envelope_error(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'tidak-terdaftar@sidoarjo.go.id',
            'password' => 'apapun',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('errors.0.code', 'AUTH_INVALID_CREDENTIALS');
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
