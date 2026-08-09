<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrumenObservasi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'instrumen_observasi';

    protected $fillable = [
        'versi',
        'nama',
        'skor_min',
        'skor_maks',
        'terkunci',
    ];

    protected function casts(): array
    {
        return [
            'versi' => 'integer',
            'skor_min' => 'integer',
            'skor_maks' => 'integer',
            'terkunci' => 'boolean',
        ];
    }

    public function butir(): HasMany
    {
        return $this->hasMany(ButirObservasi::class, 'instrumen_id');
    }
}
