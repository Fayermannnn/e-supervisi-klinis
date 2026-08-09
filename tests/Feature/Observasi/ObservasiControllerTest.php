<?php

namespace Tests\Feature\Observasi;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ObservasiControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_observasi_endpoint(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/observasi", ['butir' => []])
            ->assertStatus(401);
    }

    public function test_supervisor_can_simpan_hasil_untuk_sesi_yang_ditangani(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'instrumen_id' => $instrumen->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/observasi", [
            'butir' => [['butir_id' => $butir->id, 'skor' => 3]],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'observasi');
    }

    public function test_supervisor_cannot_simpan_hasil_untuk_sesi_supervisor_lain(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);
        Sanctum::actingAs($supervisor);

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/observasi", [
            'butir' => [['butir_id' => $butir->id, 'skor' => 3]],
        ])->assertStatus(403);
    }

    public function test_guru_cannot_simpan_hasil(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id, 'instrumen_id' => $instrumen->id]);
        Sanctum::actingAs($guru);

        $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/observasi", [
            'butir' => [['butir_id' => $butir->id, 'skor' => 3]],
        ])->assertStatus(403);
    }

    public function test_status_akhir_true_menyelesaikan_analisis(): void
    {
        $supervisor = $this->penggunaWithRole('supervisor');
        $instrumen = InstrumenObservasi::factory()->terkunci()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id]);
        $sesi = SesiSupervisi::factory()->create(['supervisor_id' => $supervisor->id, 'instrumen_id' => $instrumen->id]);
        Sanctum::actingAs($supervisor);

        $response = $this->postJson("/api/v1/sesi-supervisi/{$sesi->id}/observasi", [
            'butir' => [['butir_id' => $butir->id, 'skor' => 3]],
            'status_akhir' => true,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'dianalisis');
    }
}
