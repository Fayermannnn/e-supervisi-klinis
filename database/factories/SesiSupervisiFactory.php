<?php

namespace Database\Factories;

use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SesiSupervisi>
 */
class SesiSupervisiFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sekolah_id' => Sekolah::factory(),
            'guru_id' => Pengguna::factory(),
            'supervisor_id' => Pengguna::factory(),
            'tipe_supervisor' => 'internal',
            'status' => 'draft',
            'tanggal' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
        ];
    }
}
