<?php

namespace App\Modules\AuditLog\Services;

use App\Models\AuditLog;
use App\Models\Pengguna;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Pagination\Cursor;

class AuditLogService
{
    public function catat(
        ?Pengguna $pengguna,
        string $aksi,
        ?string $deskripsi = null,
        ?string $ipAddress = null,
        ?string $correlationId = null,
        ?string $modelType = null,
        ?string $modelId = null,
    ): AuditLog {
        return AuditLog::create([
            'pengguna_id' => $pengguna?->id,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => $ipAddress,
            'correlation_id' => $correlationId,
        ]);
    }

    /**
     * Sprint 10 backlog: "Endpoint GET /audit-log (cursor pagination)".
     * Cursor eksplisit di-decode di sini (bukan mengandalkan resolusi
     * implisit dari query string request) supaya method ini bisa dipanggil
     * konsisten baik dari Controller (HTTP request asli) maupun dari
     * Livewire (cursor tersimpan sebagai properti komponen, bukan query
     * string request per-render).
     *
     * @param  array{aksi?: ?string, dari?: ?string, sampai?: ?string}  $filters
     */
    public function list(array $filters = [], ?string $encodedCursor = null, int $perPage = 20): CursorPaginator
    {
        $cursor = $encodedCursor ? Cursor::fromEncoded($encodedCursor) : null;

        return AuditLog::query()
            ->with('pengguna')
            ->when($filters['aksi'] ?? null, fn ($q, $aksi) => $q->where('aksi', 'like', "%{$aksi}%"))
            ->when($filters['dari'] ?? null, fn ($q, $dari) => $q->whereDate('created_at', '>=', $dari))
            ->when($filters['sampai'] ?? null, fn ($q, $sampai) => $q->whereDate('created_at', '<=', $sampai))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
    }
}
