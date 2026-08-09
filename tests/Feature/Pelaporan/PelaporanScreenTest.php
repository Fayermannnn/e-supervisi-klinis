<?php

namespace Tests\Feature\Pelaporan;

use App\Livewire\Pelaporan\DashboardAdminDinas;
use App\Livewire\Pelaporan\LaporanSekolah;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class PelaporanScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_laporan_sekolah_requires_authentication(): void
    {
        $sekolah = Sekolah::factory()->create();

        $this->get("/laporan/sekolah/{$sekolah->id}")->assertRedirect(route('login'));
    }

    public function test_kepala_sekolah_sekolah_lain_tidak_bisa_membuka_laporan(): void
    {
        $kepsek = $this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => Sekolah::factory()->create()->id]);
        $this->actingAs($kepsek, 'web');

        $sekolahLain = Sekolah::factory()->create();

        Livewire::test(LaporanSekolah::class, ['sekolah' => $sekolahLain])->assertForbidden();
    }

    public function test_kepala_sekolah_bisa_membuka_laporan_sekolahnya(): void
    {
        $sekolah = Sekolah::factory()->create();
        $kepsek = $this->penggunaWithRole('kepala_sekolah', ['sekolah_id' => $sekolah->id]);
        $this->actingAs($kepsek, 'web');

        Livewire::test(LaporanSekolah::class, ['sekolah' => $sekolah])
            ->assertViewHas('nama_sekolah', $sekolah->nama_sekolah);
    }

    public function test_guru_tidak_bisa_membuka_dashboard_admin_dinas(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        Livewire::test(DashboardAdminDinas::class)->assertForbidden();
    }

    public function test_admin_dinas_bisa_membuka_dashboard(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        Livewire::test(DashboardAdminDinas::class)
            ->assertViewHas('total_sekolah');
    }
}
