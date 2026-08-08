<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationId
{
    private const HEADER = 'X-Correlation-Id';

    /**
     * Setiap request punya ID korelasi konsisten (dipakai ulang bila klien
     * sudah mengirimnya), tersedia untuk AuditLogging dan tercatat di semua
     * baris log Laravel selama request ini berlangsung.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header(self::HEADER) ?: (string) Str::uuid();

        $request->attributes->set('correlation_id', $correlationId);

        Log::withContext(['correlation_id' => $correlationId]);

        $response = $next($request);

        $response->headers->set(self::HEADER, $correlationId);

        return $response;
    }
}
