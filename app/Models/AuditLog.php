<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    /**
     * Append-only: tidak ada updated_at, tidak ada soft delete
     * (dilarang di level DB lewat trigger, lihat migration).
     */
    const UPDATED_AT = null;

    protected $table = 'audit_log';

    protected $fillable = [
        'pengguna_id',
        'aksi',
        'deskripsi',
        'model_type',
        'model_id',
        'ip_address',
        'correlation_id',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class);
    }
}
