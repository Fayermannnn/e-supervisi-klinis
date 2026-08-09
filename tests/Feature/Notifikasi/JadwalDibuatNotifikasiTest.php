<?php

namespace Tests\Feature\Notifikasi;

use App\Models\Sekolah;
use App\Modules\Perencanaan\Services\SesiSupervisiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class JadwalDibuatNotifikasiTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    /**
     * Sprint 3 backlog: "Event jadwal dibuat -> notifikasi guru & supervisor"
     * (Modul 12, dependency: NotificationService + Buat Jadwal).
     */
    public function test_buat_jadwal_mengirim_notifikasi_ke_guru_dan_supervisor(): void
    {
        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);
        $supervisor = $this->penggunaWithRole('supervisor');

        app(SesiSupervisiService::class)->buatJadwal([
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'tipe_supervisor' => 'internal',
            'tanggal' => now()->addDay()->toDateString(),
        ]);

        $this->assertDatabaseHas('notifikasi', ['pengguna_id' => $guru->id, 'judul' => 'Jadwal Supervisi Baru']);
        $this->assertDatabaseHas('notifikasi', ['pengguna_id' => $supervisor->id, 'judul' => 'Jadwal Supervisi Baru']);
    }
}
