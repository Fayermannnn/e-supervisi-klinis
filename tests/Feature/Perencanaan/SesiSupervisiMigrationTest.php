<?php

namespace Tests\Feature\Perencanaan;

use App\Models\Pengguna;
use App\Models\Sekolah;
use App\Models\SesiSupervisi;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SesiSupervisiMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sesi_supervisi_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('sesi_supervisi', [
            'id',
            'sekolah_id',
            'guru_id',
            'supervisor_id',
            'tipe_supervisor',
            'status',
            'tanggal',
            'fokus_observasi',
            'level_perkembangan_guru',
            'pendekatan_disarankan',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_sesi_supervisi_can_be_created_with_uuid_primary_key(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->assertIsString($sesi->id);
        $this->assertSame(36, strlen($sesi->id));
        $this->assertSame('draft', $sesi->status);
    }

    public function test_sesi_supervisi_soft_deletes_instead_of_hard_deleting(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $sesi->delete();

        $this->assertSoftDeleted('sesi_supervisi', ['id' => $sesi->id]);
    }

    public function test_guru_id_fk_restricts_deletion_of_pengguna_still_referenced(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->expectException(QueryException::class);

        Pengguna::query()->whereKey($sesi->guru_id)->forceDelete();
    }

    public function test_sekolah_id_fk_restricts_deletion_of_sekolah_still_referenced(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->expectException(QueryException::class);

        Sekolah::query()->whereKey($sesi->sekolah_id)->forceDelete();
    }
}
