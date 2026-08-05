<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sekolah extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'alamat',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }
}
