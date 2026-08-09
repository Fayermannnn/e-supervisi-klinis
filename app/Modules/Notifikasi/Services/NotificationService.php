<?php

namespace App\Modules\Notifikasi\Services;

use App\Models\Notifikasi;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Collection;

/**
 * Fondasi generik (Sprint 3, Modul 12) - dipanggil oleh event listener dari
 * modul manapun (Perencanaan, Observasi, RTL, dst.) yang perlu memberi tahu
 * pengguna, bukan dibangun ulang per modul.
 */
class NotificationService
{
    public function kirim(Pengguna $penerima, string $judul, string $pesan, ?string $tautan = null): Notifikasi
    {
        return Notifikasi::create([
            'pengguna_id' => $penerima->id,
            'judul' => $judul,
            'pesan' => $pesan,
            'tautan' => $tautan,
        ]);
    }

    public function jumlahBelumDibaca(Pengguna $pengguna): int
    {
        return Notifikasi::query()
            ->where('pengguna_id', $pengguna->id)
            ->where('dibaca', false)
            ->count();
    }

    /** @return Collection<int, Notifikasi> */
    public function terbaruUntuk(Pengguna $pengguna, int $limit = 5): Collection
    {
        return Notifikasi::query()
            ->where('pengguna_id', $pengguna->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function tandaiDibaca(Notifikasi $notifikasi): void
    {
        $notifikasi->update([
            'dibaca' => true,
            'dibaca_at' => now(),
        ]);
    }
}
