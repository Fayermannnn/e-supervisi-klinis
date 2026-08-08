<?php

namespace Tests\Concerns;

use App\Models\Pengguna;
use Database\Seeders\RoleAndPermissionSeeder;

trait InteractsWithRoles
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function penggunaWithRole(string $role, array $attributes = []): Pengguna
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $pengguna = Pengguna::factory()->create($attributes);
        $pengguna->assignRole($role);

        return $pengguna;
    }
}
