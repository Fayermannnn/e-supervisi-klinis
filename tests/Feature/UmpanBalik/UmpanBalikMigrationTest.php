<?php

namespace Tests\Feature\UmpanBalik;

use App\Models\SesiSupervisi;
use App\Models\UmpanBalik;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UmpanBalikMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_umpan_balik_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('umpan_balik', [
            'id', 'sesi_id', 'kekuatan', 'area_pengembangan', 'rekomendasi',
            'refleksi_guru', 'pendekatan_dipakai', 'terlambat', 'tanggal_diisi',
            'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_sesi_id_must_be_unique(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);

        $this->expectException(QueryException::class);

        UmpanBalik::factory()->create(['sesi_id' => $sesi->id]);
    }

    public function test_sesi_id_fk_restricts_deletion_of_sesi_still_referenced(): void
    {
        $umpanBalik = UmpanBalik::factory()->create();

        $this->expectException(QueryException::class);

        SesiSupervisi::query()->whereKey($umpanBalik->sesi_id)->forceDelete();
    }
}
