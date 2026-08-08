<?php

namespace Tests\Concerns;

use App\Models\Pengguna;
use App\Modules\Auth\Services\TwoFactorService;
use Database\Seeders\RoleAndPermissionSeeder;

trait InteractsWithRoles
{
    /**
     * @param  array<string, mixed>  $attributes
     *
     * $confirmTwoFactor: peran yang wajib 2FA (admin_dinas, super_admin) otomatis
     * dianggap sudah mengaktifkan 2FA, supaya test yang tidak menguji alur 2FA itu
     * sendiri tidak ikut terjegal EnsureTwoFactorIsConfigured. Set false untuk
     * menguji skenario "belum mengaktifkan 2FA".
     */
    protected function penggunaWithRole(string $role, array $attributes = [], bool $confirmTwoFactor = true): Pengguna
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $pengguna = Pengguna::factory()->create($attributes);
        $pengguna->assignRole($role);

        if ($confirmTwoFactor && app(TwoFactorService::class)->wajib2fa($pengguna)) {
            $pengguna->forceFill(['two_factor_confirmed_at' => now()])->save();
        }

        return $pengguna->fresh();
    }
}
