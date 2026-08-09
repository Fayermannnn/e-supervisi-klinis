<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UmpanBalik extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'umpan_balik';

    protected $fillable = [
        'sesi_id',
        'kekuatan',
        'area_pengembangan',
        'rekomendasi',
        'refleksi_guru',
        'pendekatan_dipakai',
        'terlambat',
        'tanggal_diisi',
    ];

    protected function casts(): array
    {
        return [
            'terlambat' => 'boolean',
            'tanggal_diisi' => 'datetime',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiSupervisi::class, 'sesi_id');
    }
}
