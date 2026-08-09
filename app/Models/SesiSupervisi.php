<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SesiSupervisi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sesi_supervisi';

    protected $fillable = [
        'sekolah_id',
        'guru_id',
        'supervisor_id',
        'instrumen_id',
        'tipe_supervisor',
        'status',
        'tanggal',
        'fokus_observasi',
        'level_perkembangan_guru',
        'pendekatan_disarankan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'guru_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'supervisor_id');
    }

    public function instrumen(): BelongsTo
    {
        return $this->belongsTo(InstrumenObservasi::class, 'instrumen_id');
    }
}
