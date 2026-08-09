<?php

namespace Database\Factories;

use App\Models\Notifikasi;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notifikasi>
 */
class NotifikasiFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'pengguna_id' => Pengguna::factory(),
            'judul' => fake()->sentence(3),
            'pesan' => fake()->sentence(10),
            'tautan' => null,
            'dibaca' => false,
        ];
    }
}
