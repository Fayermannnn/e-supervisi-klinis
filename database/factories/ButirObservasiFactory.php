<?php

namespace Database\Factories;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ButirObservasi>
 */
class ButirObservasiFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'instrumen_id' => InstrumenObservasi::factory(),
            'kode' => 'A'.fake()->numberBetween(1, 2),
            'dimensi' => 'A. Pembukaan Pembelajaran',
            'teks' => fake()->sentence(),
            'definisi_operasional' => fake()->sentence(15),
            'bobot' => 5.00,
        ];
    }
}
