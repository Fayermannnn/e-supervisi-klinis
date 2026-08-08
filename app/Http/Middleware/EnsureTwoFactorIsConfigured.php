<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsConfigured
{
    public function __construct(private readonly TwoFactorService $twoFactorService) {}

    /**
     * Peran yang wajib 2FA (admin_dinas, super_admin) diarahkan ke layar
     * aktivasi selama belum mengaktifkan verifikasi dua langkah.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        if (
            $pengguna
            && $this->twoFactorService->wajib2fa($pengguna)
            && ! $this->twoFactorService->sudahAktif($pengguna)
            && ! $request->routeIs('two-factor.setup')
            && ! $request->routeIs('logout')
            && ! $request->routeIs('*livewire*')
        ) {
            return redirect()->route('two-factor.setup');
        }

        return $next($request);
    }
}
