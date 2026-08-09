<?php

namespace Database\Factories;

use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HasilObservasi>
 */
class HasilObservasiFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sesi_id' => SesiSupervisi::factory(),
            'butir_id' => ButirObservasi::factory(),
            'skor' => fake()->numberBetween(1, 4),
        ];
    }
}
