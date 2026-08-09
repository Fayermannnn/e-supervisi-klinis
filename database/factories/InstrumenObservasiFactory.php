<?php

namespace Database\Factories;

use App\Models\InstrumenObservasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstrumenObservasi>
 */
class InstrumenObservasiFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'versi' => fake()->unique()->numberBetween(1, 1000),
            'nama' => 'Instrumen Observasi Kelas v'.fake()->numberBetween(1, 10),
            'skor_min' => 1,
            'skor_maks' => 4,
            'terkunci' => false,
        ];
    }

    public function terkunci(): static
    {
        return $this->state(fn () => ['terkunci' => true]);
    }
}
