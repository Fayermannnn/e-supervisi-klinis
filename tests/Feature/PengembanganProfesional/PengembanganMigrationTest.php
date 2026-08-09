<?php

namespace Tests\Feature\PengembanganProfesional;

use App\Models\ButirObservasi;
use App\Models\MateriPengembangan;
use App\Models\Pengguna;
use App\Models\RekomendasiPengembangan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PengembanganMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_materi_pengembangan_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('materi_pengembangan', [
            'id', 'judul', 'kategori', 'tautan_atau_deskripsi', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_rekomendasi_pengembangan_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('rekomendasi_pengembangan', [
            'id', 'pengguna_id', 'sesi_id', 'materi_id', 'sumber', 'status',
            'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_default_status_belum(): void
    {
        $rekomendasi = RekomendasiPengembangan::factory()->create();

        $this->assertSame('belum', $rekomendasi->status);
    }

    public function test_materi_id_fk_restricts_deletion_of_materi_still_referenced(): void
    {
        $rekomendasi = RekomendasiPengembangan::factory()->create();

        $this->expectException(QueryException::class);

        MateriPengembangan::query()->whereKey($rekomendasi->materi_id)->forceDelete();
    }

    public function test_pengguna_id_fk_restricts_deletion_of_pengguna_still_referenced(): void
    {
        $rekomendasi = RekomendasiPengembangan::factory()->create();

        $this->expectException(QueryException::class);

        Pengguna::query()->whereKey($rekomendasi->pengguna_id)->forceDelete();
    }

    public function test_butir_observasi_materi_materi_id_now_has_fk(): void
    {
        $butir = ButirObservasi::factory()->create();
        $materi = MateriPengembangan::factory()->create();
        DB::table('butir_observasi_materi')->insert(['butir_observasi_id' => $butir->id, 'materi_id' => $materi->id]);

        $this->expectException(QueryException::class);

        MateriPengembangan::query()->whereKey($materi->id)->forceDelete();
    }
}
