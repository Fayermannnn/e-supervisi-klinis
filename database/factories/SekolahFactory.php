<?php

namespace Database\Factories;

use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sekolah>
 */
class SekolahFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'nama_sekolah' => 'SMP Negeri '.fake()->unique()->numberBetween(1, 40).' Sidoarjo',
            'npsn' => fake()->unique()->numerify('##########'),
            'alamat' => fake()->address(),
            'status_aktif' => true,
        ];
    }
}
