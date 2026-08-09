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
}
