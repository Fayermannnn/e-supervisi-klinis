<?php

namespace Tests\Feature\Observasi;

use App\Livewire\Observasi\FormObservasi;
use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class FormObservasiScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_form_observasi_requires_authentication(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->get("/sesi-supervisi/{$sesi->id}/observasi")->assertRedirect(route('login'));
    }

    public function test_supervisor_cannot_open_form_for_sesi_supervisor_lain(): void
    {
        $this->actingAs($this->penggunaWithRole('supervisor'), 'web');

        $sesi = SesiSupervisi::factory()->create();

        Livewire::test(FormObservasi::class, ['sesiSupervisi' => $sesi])->assertForbidden();
    }

    public function test_form_observasi_prefills_existing_scores(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $this->actingAs($supervisor, 'web');

        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 3]);

        Livewire::test(FormObservasi::class, ['sesiSupervisi' => $sesi])
            ->assertSet("skor.{$butir->id}", 3);
    }

    public function test_simpan_draft_menyimpan_sebagian_skor_tanpa_finalisasi(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $this->actingAs($supervisor, 'web');

        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'instrumen_id' => $instrumen->id]);

        Livewire::test(FormObservasi::class, ['sesiSupervisi' => $sesi])
            ->set("skor.{$butir->id}", 3)
            ->call('simpanDraft');

        $this->assertDatabaseHas('sesi_supervisi', ['id' => $sesi->id, 'status' => 'observasi']);
        $this->assertDatabaseHas('hasil_observasi', ['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 3]);
    }

    public function test_selesai_memfinalisasi_dan_redirect(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $this->actingAs($supervisor, 'web');

        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'instrumen_id' => $instrumen->id]);

        Livewire::test(FormObservasi::class, ['sesiSupervisi' => $sesi])
            ->set("skor.{$butir->id}", 4)
            ->call('selesai')
            ->assertRedirect(route('app.sesi-supervisi.index'));

        $this->assertDatabaseHas('sesi_supervisi', ['id' => $sesi->id, 'status' => 'dianalisis']);
    }
}
