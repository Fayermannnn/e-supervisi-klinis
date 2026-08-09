<?php

namespace Tests\Feature\Perencanaan;

use App\Livewire\Perencanaan\BuatJadwal;
use App\Livewire\Perencanaan\Index;
use App\Livewire\Perencanaan\PraObservasi;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class SesiSupervisiScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_sesi_supervisi_index_requires_authentication(): void
    {
        $this->get('/sesi-supervisi')->assertRedirect(route('login'));
    }

    public function test_guru_sees_only_own_sesi_on_index(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $this->actingAs($guru, 'web');

        $sesiSendiri = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        SesiSupervisi::factory()->create();

        $this->get('/sesi-supervisi')
            ->assertOk()
            ->assertSeeLivewire(Index::class)
            ->assertSee($sesiSendiri->tanggal->format('d/m/Y'));
    }

    public function test_guru_does_not_see_buat_jadwal_link(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $this->get('/sesi-supervisi')->assertDontSee('Buat Jadwal');
    }

    public function test_guru_cannot_open_buat_jadwal_screen(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $this->get('/sesi-supervisi/buat-jadwal')->assertForbidden();
    }

    public function test_supervisor_can_create_jadwal_via_livewire(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $this->actingAs($supervisor, 'web');

        $sekolah = Sekolah::factory()->create();
        $guru = $this->penggunaWithRole('guru', ['sekolah_id' => $sekolah->id]);

        Livewire::test(BuatJadwal::class)
            ->set('guru_id', $guru->id)
            ->set('tipe_supervisor', 'internal')
            ->set('tanggal', now()->addDay()->toDateString())
            ->call('simpan')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('sesi_supervisi', [
            'guru_id' => $guru->id,
            'supervisor_id' => $supervisor->id,
            'status' => 'dijadwalkan',
        ]);
    }

    public function test_supervisor_can_isi_pra_observasi_via_livewire(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $this->actingAs($supervisor, 'web');

        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'status' => 'dijadwalkan']);

        Livewire::test(PraObservasi::class, ['sesiSupervisi' => $sesi])
            ->set('fokus_observasi', 'Keterlibatan siswa dalam diskusi kelompok')
            ->call('simpan')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('sesi_supervisi', [
            'id' => $sesi->id,
            'status' => 'pra_observasi',
            'fokus_observasi' => 'Keterlibatan siswa dalam diskusi kelompok',
        ]);
    }

    public function test_supervisor_cannot_isi_pra_observasi_sesi_supervisor_lain(): void
    {
        $this->actingAs($this->penggunaWithRole('supervisor'), 'web');

        $sesi = SesiSupervisi::factory()->create(['status' => 'dijadwalkan']);

        Livewire::test(PraObservasi::class, ['sesiSupervisi' => $sesi])
            ->assertForbidden();
    }
}
