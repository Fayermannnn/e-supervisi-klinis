<?php

namespace Tests\Feature\UmpanBalik;

use App\Livewire\UmpanBalik\FormUmpanBalik;
use App\Livewire\UmpanBalik\RefleksiGuru;
use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class UmpanBalikScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_form_umpan_balik_requires_authentication(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->get("/sesi-supervisi/{$sesi->id}/umpan-balik")->assertRedirect(route('login'));
    }

    public function test_guru_cannot_open_form_umpan_balik(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        $this->actingAs($guru, 'web');

        Livewire::test(FormUmpanBalik::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }

    public function test_form_umpan_balik_defaults_pendekatan_ke_saran_sistem(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'pendekatan_disarankan' => 'collaborative']);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormUmpanBalik::class, ['sesiSupervisi' => $sesi])
            ->assertSet('pendekatan_dipakai', 'collaborative');
    }

    public function test_label_berubah_mengikuti_pendekatan_yang_dipilih(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormUmpanBalik::class, ['sesiSupervisi' => $sesi])
            ->set('pendekatan_dipakai', 'nondirective')
            ->assertSee('Menurut Anda, Apa yang Sudah Berjalan Baik?');
    }

    public function test_supervisor_bisa_mengisi_umpan_balik_via_livewire(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        $this->actingAs($supervisor, 'web');

        Livewire::test(FormUmpanBalik::class, ['sesiSupervisi' => $sesi])
            ->set('kekuatan', 'Kuat di pedagogik')
            ->set('area_pengembangan', 'Pengelolaan waktu')
            ->set('rekomendasi', 'Latihan pacing')
            ->call('simpan')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('umpan_balik', ['sesi_id' => $sesi->id, 'kekuatan' => 'Kuat di pedagogik']);
    }

    public function test_refleksi_guru_requires_authentication(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->get("/sesi-supervisi/{$sesi->id}/refleksi")->assertRedirect(route('login'));
    }

    public function test_guru_bisa_mengisi_refleksi_via_livewire(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id]);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id, 'kekuatan' => 'Baik']);
        $this->actingAs($guru, 'web');

        Livewire::test(RefleksiGuru::class, ['sesiSupervisi' => $sesi])
            ->assertSet('kekuatan', 'Baik')
            ->set('refleksi_guru', 'Terima kasih atas masukannya.')
            ->call('simpan')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('umpan_balik', ['sesi_id' => $sesi->id, 'refleksi_guru' => 'Terima kasih atas masukannya.']);
    }

    public function test_supervisor_tidak_bisa_membuka_refleksi_guru(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id]);
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);
        $this->actingAs($supervisor, 'web');

        Livewire::test(RefleksiGuru::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }
}
