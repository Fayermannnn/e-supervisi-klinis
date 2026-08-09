<?php

namespace Database\Factories;

use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RencanaTindakLanjut>
 */
class RencanaTindakLanjutFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sesi_id' => SesiSupervisi::factory(),
            'deskripsi' => fake()->paragraph(),
            'target_waktu' => fake()->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'kategori' => fake()->randomElement(['pedagogik', 'kepribadian', 'sosial', 'profesional']),
            'status' => 'belum',
            'tanggal_diisi' => now(),
        ];
    }
}
