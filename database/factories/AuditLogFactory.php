<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'pengguna_id' => Pengguna::factory(),
            'aksi' => 'AKSES_DITOLAK',
            'deskripsi' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'correlation_id' => fake()->uuid(),
        ];
    }
}
