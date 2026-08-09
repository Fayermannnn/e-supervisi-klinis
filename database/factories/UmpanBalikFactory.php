<?php

namespace Database\Factories;

use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmpanBalik>
 */
class UmpanBalikFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sesi_id' => SesiSupervisi::factory(),
            'kekuatan' => fake()->paragraph(),
            'area_pengembangan' => fake()->paragraph(),
            'rekomendasi' => fake()->paragraph(),
            'terlambat' => false,
            'tanggal_diisi' => now(),
        ];
    }
}
