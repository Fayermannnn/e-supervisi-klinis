<?php

namespace App\Modules\AuditLog\Services;

use App\Models\AuditLog;
use App\Models\Pengguna;

class AuditLogService
{
    public function catat(
        ?Pengguna $pengguna,
        string $aksi,
        ?string $deskripsi = null,
        ?string $ipAddress = null,
        ?string $correlationId = null,
    ): AuditLog {
        return AuditLog::create([
            'pengguna_id' => $pengguna?->id,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'ip_address' => $ipAddress,
            'correlation_id' => $correlationId,
        ]);
    }
}
