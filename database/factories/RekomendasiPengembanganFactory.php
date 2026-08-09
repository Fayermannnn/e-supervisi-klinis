<?php

namespace Database\Factories;

use App\Models\MateriPengembangan;
use App\Models\Pengguna;
use App\Models\RekomendasiPengembangan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RekomendasiPengembangan>
 */
class RekomendasiPengembanganFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'pengguna_id' => Pengguna::factory(),
            'sesi_id' => null,
            'materi_id' => MateriPengembangan::factory(),
            'sumber' => 'manual',
            'status' => 'belum',
        ];
    }
}
