<?php

namespace Tests\Feature\Sekolah;

use App\Models\Sekolah;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SekolahMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sekolah_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('sekolah', [
            'id',
            'nama_sekolah',
            'npsn',
            'alamat',
            'status_aktif',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_sekolah_can_be_created_with_uuid_primary_key(): void
    {
        $sekolah = Sekolah::factory()->create();

        $this->assertIsString($sekolah->id);
        $this->assertSame(36, strlen($sekolah->id));
        $this->assertTrue($sekolah->status_aktif);
    }

    public function test_npsn_must_be_unique(): void
    {
        Sekolah::factory()->create(['npsn' => '20501234']);

        $this->expectException(QueryException::class);

        Sekolah::factory()->create(['npsn' => '20501234']);
    }

    public function test_sekolah_soft_deletes_instead_of_hard_deleting(): void
    {
        $sekolah = Sekolah::factory()->create();

        $sekolah->delete();

        $this->assertSoftDeleted('sekolah', ['id' => $sekolah->id]);
    }
}
