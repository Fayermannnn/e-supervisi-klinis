<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RencanaTindakLanjut extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'rencana_tindak_lanjut';

    protected $fillable = [
        'sesi_id',
        'deskripsi',
        'target_waktu',
        'kategori',
        'status',
        'tanggal_diisi',
    ];

    protected function casts(): array
    {
        return [
            'target_waktu' => 'date',
            'tanggal_diisi' => 'datetime',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiSupervisi::class, 'sesi_id');
    }
}
