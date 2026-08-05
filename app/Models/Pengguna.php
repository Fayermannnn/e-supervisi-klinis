<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'nip_nuptk',
        'no_telepon',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status_aktif' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
