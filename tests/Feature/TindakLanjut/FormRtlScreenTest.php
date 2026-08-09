<?php

namespace Tests\Feature\TindakLanjut;

use App\Livewire\TindakLanjut\FormRtl;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class FormRtlScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_form_rtl_requires_authentication(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->get("/sesi-supervisi/{$sesi->id}/rtl")->assertRedirect(route('login'));
    }

    public function test_guru_cannot_open_form_rtl(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        $this->actingAs($guru, 'web');

        Livewire::test(FormRtl::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }

    public function test_konteks_umpan_balik_tetap_tampil_di_form_rtl(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'kekuatan' => 'Kekuatan unik untuk diuji']);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormRtl::class, ['sesiSupervisi' => $sesi])
            ->assertSee('Kekuatan unik untuk diuji');
    }

    public function test_supervisor_bisa_mengisi_rtl_via_livewire(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormRtl::class, ['sesiSupervisi' => $sesi])
            ->set('deskripsi', 'Latihan manajemen kelas')
            ->set('target_waktu', now()->addWeek()->toDateString())
            ->set('kategori', 'pedagogik')
            ->call('simpan')
            ->assertSet('sudahAda', true);

        $this->assertDatabaseHas('rencana_tindak_lanjut', ['sesi_id' => $sesi->id, 'deskripsi' => 'Latihan manajemen kelas']);
    }

    public function test_tandai_selesai_hanya_muncul_setelah_rtl_tersimpan(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormRtl::class, ['sesiSupervisi' => $sesi])
            ->assertDontSee('Tandai Selesai');
    }

    public function test_selesaikan_dari_layar_rtl_redirect_ke_index(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormRtl::class, ['sesiSupervisi' => $sesi])
            ->set('deskripsi', 'Rencana')
            ->set('target_waktu', now()->addWeek()->toDateString())
            ->call('simpan')
            ->call('selesaikan')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('sesi_supervisi', ['id' => $sesi->id, 'status' => 'selesai']);
    }
}
