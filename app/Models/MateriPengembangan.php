<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MateriPengembangan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'materi_pengembangan';

    protected $fillable = [
        'judul',
        'kategori',
        'tautan_atau_deskripsi',
    ];

    public function rekomendasi(): HasMany
    {
        return $this->hasMany(RekomendasiPengembangan::class, 'materi_id');
    }
}
