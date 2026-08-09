<?php

namespace App\Providers;

use App\Models\Pengguna;
use App\Policies\LaporanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (Pengguna $pengguna, string $ability) {
            return $pengguna->hasRole('super_admin') ? true : null;
        });

        // Sprint 9 (Modul 11): abilities Pelaporan yang tidak terikat objek
        // Eloquent (LaporanPolicy, lihat doc-block class-nya).
        Gate::define('laporan.viewDashboard', [LaporanPolicy::class, 'viewDashboard']);
        Gate::define('laporan.drillDownIndividual', [LaporanPolicy::class, 'drillDownIndividual']);
    }
}
