<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekomendasiPengembangan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'rekomendasi_pengembangan';

    protected $fillable = [
        'pengguna_id',
        'sesi_id',
        'materi_id',
        'sumber',
        'status',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class);
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiSupervisi::class, 'sesi_id');
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(MateriPengembangan::class, 'materi_id');
    }
}
