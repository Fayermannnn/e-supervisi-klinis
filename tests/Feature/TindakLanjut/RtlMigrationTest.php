<?php

namespace Tests\Feature\TindakLanjut;

use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RtlMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rtl_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('rencana_tindak_lanjut', [
            'id', 'sesi_id', 'deskripsi', 'target_waktu', 'kategori', 'status',
            'tanggal_diisi', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_sesi_id_must_be_unique(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id]);

        $this->expectException(QueryException::class);

        RencanaTindakLanjut::factory()->create(['sesi_id' => $sesi->id]);
    }

    public function test_default_status_belum(): void
    {
        $rtl = RencanaTindakLanjut::factory()->create();

        $this->assertSame('belum', $rtl->status);
    }

    public function test_sesi_id_fk_restricts_deletion_of_sesi_still_referenced(): void
    {
        $rtl = RencanaTindakLanjut::factory()->create();

        $this->expectException(QueryException::class);

        SesiSupervisi::query()->whereKey($rtl->sesi_id)->forceDelete();
    }
}
