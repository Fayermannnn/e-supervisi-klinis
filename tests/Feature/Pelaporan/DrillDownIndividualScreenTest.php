<?php

namespace Tests\Feature\Pelaporan;

use App\Livewire\Pelaporan\DrillDownIndividual;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class DrillDownIndividualScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_kepala_sekolah_tidak_bisa_membuka_drill_down(): void
    {
        $this->actingAs($this->penggunaWithRole('kepala_sekolah'), 'web');
        $sesi = SesiSupervisi::factory()->create();

        Livewire::test(DrillDownIndividual::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }

    /**
     * TC-PELAPORAN-001 (BR-08): justifikasi wajib sebelum detail tampil.
     */
    public function test_admin_dinas_tidak_bisa_lihat_tanpa_justifikasi(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');
        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'kekuatan' => 'Rahasia formatif']);

        Livewire::test(DrillDownIndividual::class, ['sesiSupervisi' => $sesi])
            ->set('justifikasi', 'pendek')
            ->call('lihat')
            ->assertHasErrors('justifikasi');
    }

    public function test_admin_dinas_bisa_lihat_detail_dengan_justifikasi_dan_tercatat_di_audit_log(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        $this->actingAs($admin, 'web');
        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'kekuatan' => 'Rahasia formatif']);

        Livewire::test(DrillDownIndividual::class, ['sesiSupervisi' => $sesi])
            ->set('justifikasi', 'Audit rutin triwulan atas permintaan Kepala Dinas.')
            ->call('lihat')
            ->assertSet('hasil.umpan_balik.kekuatan', 'Rahasia formatif');

        $this->assertDatabaseHas('audit_log', [
            'pengguna_id' => $admin->id,
            'aksi' => 'LAPORAN_DRILL_DOWN_INDIVIDUAL',
        ]);
    }
}
