<?php

namespace Database\Factories;

use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'nip_nuptk' => fake()->numerify('####################'),
            'no_telepon' => fake()->numerify('08##########'),
            'status_aktif' => true,
            'email_verified_at' => now(),
        ];
    }
}
