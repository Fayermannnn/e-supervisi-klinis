<?php

namespace Tests\Feature\Instrumen;

use App\Models\ButirObservasi;
use App\Models\InstrumenObservasi;
use App\Models\SesiSupervisi;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstrumenMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_instrumen_observasi_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('instrumen_observasi', [
            'id', 'versi', 'nama', 'skor_min', 'skor_maks', 'terkunci',
            'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_butir_observasi_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('butir_observasi', [
            'id', 'instrumen_id', 'kode', 'dimensi', 'teks', 'definisi_operasional', 'bobot',
            'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_butir_observasi_materi_pivot_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('butir_observasi_materi', ['butir_observasi_id', 'materi_id']));
    }

    public function test_instrumen_can_be_created_with_default_skala_1_sampai_4(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();

        $this->assertSame(1, $instrumen->skor_min);
        $this->assertSame(4, $instrumen->skor_maks);
        $this->assertFalse($instrumen->terkunci);
    }

    public function test_butir_id_fk_restricts_deletion_of_instrumen_still_referenced(): void
    {
        $butir = ButirObservasi::factory()->create();

        $this->expectException(QueryException::class);

        InstrumenObservasi::query()->whereKey($butir->instrumen_id)->forceDelete();
    }

    public function test_sesi_supervisi_has_instrumen_id_column(): void
    {
        $this->assertTrue(Schema::hasColumn('sesi_supervisi', 'instrumen_id'));
    }

    public function test_sesi_supervisi_instrumen_id_fk_restricts_deletion(): void
    {
        $instrumen = InstrumenObservasi::factory()->create();
        SesiSupervisi::factory()->create(['instrumen_id' => $instrumen->id]);

        $this->expectException(QueryException::class);

        InstrumenObservasi::query()->whereKey($instrumen->id)->forceDelete();
    }
}
