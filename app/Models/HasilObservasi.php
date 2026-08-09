<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HasilObservasi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'hasil_observasi';

    protected $fillable = [
        'sesi_id',
        'butir_id',
        'skor',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiSupervisi::class, 'sesi_id');
    }

    public function butir(): BelongsTo
    {
        return $this->belongsTo(ButirObservasi::class, 'butir_id');
    }
}
