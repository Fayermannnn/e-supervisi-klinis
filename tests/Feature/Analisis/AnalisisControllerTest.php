<?php

namespace Tests\Feature\Analisis;

use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class AnalisisControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_skor_endpoint(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->getJson("/api/v1/sesi-supervisi/{$sesi->id}/skor")->assertStatus(401);
    }

    public function test_guru_can_view_skor_untuk_sesi_miliknya(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $instrumen = InstrumenObservasi::factory()->create();
        $butir = ButirObservasi::factory()->create(['instrumen_id' => $instrumen->id, 'bobot' => 100]);
        $sesi = SesiSupervisi::factory()->create(['guru_id' => $guru->id, 'instrumen_id' => $instrumen->id]);
        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id, 'skor' => 4]);
        Sanctum::actingAs($guru);

        $response = $this->getJson("/api/v1/sesi-supervisi/{$sesi->id}/skor");

        $response->assertStatus(200)
            ->assertJsonPath('data.total', 100)
            ->assertJsonCount(1, 'data.breakdown');
    }

    public function test_guru_cannot_view_skor_sesi_orang_lain(): void
    {
        $guru = $this->penggunaWithRole('guru');
        $sesi = SesiSupervisi::factory()->create();
        Sanctum::actingAs($guru);

        $this->getJson("/api/v1/sesi-supervisi/{$sesi->id}/skor")->assertStatus(403);
    }
}
