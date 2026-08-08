<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    /**
     * Gerbang permission level-modul (defense-in-depth di depan Service).
     * Scoping per objek (mis. Kepala Sekolah vs sekolahnya sendiri)
     * ditegakkan terpisah di Policy.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $pengguna = $request->user();

        if (! $pengguna || ! $pengguna->can($permission)) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    'FORBIDDEN',
                    'Anda tidak memiliki izin untuk mengakses sumber daya ini.',
                    403
                );
            }

            abort(403, 'Anda tidak memiliki izin untuk mengakses sumber daya ini.');
        }

        return $next($request);
    }
}
