<?php

namespace Database\Factories;

use App\Models\MateriPengembangan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MateriPengembangan>
 */
class MateriPengembanganFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(4),
            'kategori' => fake()->randomElement(['pedagogik', 'kepribadian', 'sosial', 'profesional']),
            'tautan_atau_deskripsi' => fake()->sentence(10),
        ];
    }
}
