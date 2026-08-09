<?php

namespace Tests\Feature\Observasi;

use App\Models\ButirObservasi;
use App\Models\HasilObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HasilObservasiMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hasil_observasi_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('hasil_observasi', [
            'id', 'sesi_id', 'butir_id', 'skor', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_hasil_observasi_can_be_created(): void
    {
        $hasil = HasilObservasi::factory()->create(['skor' => 3]);

        $this->assertSame(3, $hasil->skor);
    }

    public function test_sesi_and_butir_combination_must_be_unique(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $butir = ButirObservasi::factory()->create();

        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id]);

        $this->expectException(QueryException::class);

        HasilObservasi::factory()->create(['sesi_id' => $sesi->id, 'butir_id' => $butir->id]);
    }

    public function test_sesi_id_fk_restricts_deletion_of_sesi_still_referenced(): void
    {
        $hasil = HasilObservasi::factory()->create();

        $this->expectException(QueryException::class);

        SesiSupervisi::query()->whereKey($hasil->sesi_id)->forceDelete();
    }
}
