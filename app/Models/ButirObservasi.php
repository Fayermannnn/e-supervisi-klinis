<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ButirObservasi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'butir_observasi';

    protected $fillable = [
        'instrumen_id',
        'kode',
        'dimensi',
        'teks',
        'definisi_operasional',
        'bobot',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
        ];
    }

    public function instrumen(): BelongsTo
    {
        return $this->belongsTo(InstrumenObservasi::class, 'instrumen_id');
    }
}
