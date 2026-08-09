<?php

namespace Tests\Feature\Analisis;

use App\Livewire\Analisis\RingkasanSkor;
use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class RingkasanSkorScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_ringkasan_skor_requires_authentication(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->get("/sesi-supervisi/{$sesi->id}/skor")->assertRedirect(route('login'));
    }

    public function test_guru_lain_tidak_bisa_membuka_ringkasan_skor_sesi_orang_lain(): void
    {
        $this->actingAs($this->penggunaWithRole('guru'), 'web');

        $sesi = SesiSupervisi::factory()->create();

        Livewire::test(RingkasanSkor::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }

    public function test_guru_bisa_melihat_ringkasan_skor_miliknya(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $this->actingAs($guru, 'web');

        $instrumen = InstrumenObservasi::factory()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id, 'instrumen_id' => $instrumen->id, 'status' => 'dianalisis']);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 4]);

        Livewire::test(RingkasanSkor::class, ['sesiSupervisi' => $sesi])
            ->assertViewHas('total', 100.0);
    }
}
