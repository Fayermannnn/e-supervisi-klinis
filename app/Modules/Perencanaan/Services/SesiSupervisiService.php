<?php

namespace App\Modules\Perencanaan\Services;

use App\Events\SesiSupervisiDijadwalkan;
use App\Exceptions\Br01FokusObservasiKosongException;
use App\Exceptions\Br02SupervisorEqualsGuruException;
use App\Exceptions\SesiSupervisiNotFoundException;
use App\Models\Pengguna;
use App\Models\SesiSupervisi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SesiSupervisiService
{
    /**
     * Scoping visibilitas (Policy::view): Guru -> guruId, Supervisor ->
     * supervisorId, Kepala Sekolah -> sekolahId. Admin Dinas/Super Admin
     * memanggil tanpa filter (akses agregat kabupaten).
     */
    public function list(
        int $perPage = 15,
        ?string $guruId = null,
        ?string $supervisorId = null,
        ?string $sekolahId = null,
    ): LengthAwarePaginator {
        return SesiSupervisi::query()
            ->with(['guru', 'supervisor', 'sekolah', 'umpanBalik', 'rtl'])
            ->when($guruId, fn ($query) => $query->where('guru_id', $guruId))
            ->when($supervisorId, fn ($query) => $query->where('supervisor_id', $supervisorId))
            ->when($sekolahId, fn ($query) => $query->where('sekolah_id', $sekolahId))
            ->latest('tanggal')
            ->paginate($perPage);
    }

    /** @throws SesiSupervisiNotFoundException */
    public function find(string $id): SesiSupervisi
    {
        $sesi = SesiSupervisi::find($id);

        if (! $sesi) {
            throw new SesiSupervisiNotFoundException;
        }

        return $sesi;
    }

    /**
     * BR-02: Supervisor tidak boleh sama dengan guru yang diobservasi.
     * Ditegakkan di sini (defense pertama) selain DB CHECK constraint
     * (defense kedua, lihat migrasi sesi_supervisi).
     *
     * @param  array<string, mixed>  $data  guru_id, supervisor_id, tipe_supervisor, tanggal
     *
     * @throws Br02SupervisorEqualsGuruException
     */
    public function buatJadwal(array $data): SesiSupervisi
    {
        if ($data['guru_id'] === $data['supervisor_id']) {
            throw new Br02SupervisorEqualsGuruException;
        }

        $guru = Pengguna::findOrFail($data['guru_id']);

        $sesi = SesiSupervisi::create([
            'sekolah_id' => $guru->sekolah_id,
            'guru_id' => $data['guru_id'],
            'supervisor_id' => $data['supervisor_id'],
            'tipe_supervisor' => $data['tipe_supervisor'],
            'tanggal' => $data['tanggal'],
            'status' => 'dijadwalkan',
        ]);

        SesiSupervisiDijadwalkan::dispatch($sesi);

        return $sesi;
    }

    /**
     * BR-01: Pra-observasi wajib diisi (fokus observasi) sebelum status sesi
     * berpindah ke "pra_observasi". Field level_perkembangan_guru bersifat
     * opsional (Developmental Supervision, Bagian I.8 SDD) dan tidak
     * memblokir submit bila kosong.
     *
     * @param  array<string, mixed>  $data  fokus_observasi, level_perkembangan_guru (opsional)
     *
     * @throws Br01FokusObservasiKosongException
     * @throws SesiSupervisiNotFoundException
     */
    public function isiPraObservasi(string $sesiId, array $data): SesiSupervisi
    {
        if (empty($data['fokus_observasi'])) {
            throw new Br01FokusObservasiKosongException;
        }

        $sesi = $this->find($sesiId);

        $level = $data['level_perkembangan_guru'] ?? null;

        $sesi->update([
            'fokus_observasi' => $data['fokus_observasi'],
            'level_perkembangan_guru' => $level,
            'pendekatan_disarankan' => $level ? $this->petakanPendekatan($level) : null,
            'status' => 'pra_observasi',
        ]);

        return $sesi;
    }

    /**
     * Developmental Supervision (Glickman, Gordon & Ross-Gordon, 2007):
     * pemetaan level perkembangan guru -> pendekatan yang disarankan,
     * bukan wajib dipakai (BR-10, supervisor boleh menyimpang - lihat
     * Sprint 7 pendekatan_dipakai).
     */
    private function petakanPendekatan(string $level): string
    {
        return match ($level) {
            'rendah' => 'directive_control',
            'sedang_rendah' => 'directive_informational',
            'sedang_tinggi' => 'collaborative',
            'tinggi' => 'nondirective',
            default => 'collaborative',
        };
    }
}
