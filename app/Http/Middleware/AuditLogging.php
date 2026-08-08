<?php

namespace App\Http\Middleware;

use App\Modules\AuditLog\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogging
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Setiap respons 403 (permission middleware, Policy, atau abort manual)
     * tercatat ke audit_log.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 403) {
            $this->auditLogService->catat(
                pengguna: $request->user(),
                aksi: 'AKSES_DITOLAK',
                deskripsi: $request->method().' '.$request->path(),
                ipAddress: $request->ip(),
                correlationId: $request->attributes->get('correlation_id'),
            );
        }

        return $response;
    }
}
