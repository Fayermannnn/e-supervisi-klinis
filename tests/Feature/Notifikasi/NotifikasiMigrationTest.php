<?php

namespace Tests\Feature\Notifikasi;

use App\Models\Notifikasi;
use App\Models\Pengguna;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotifikasiMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifikasi_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('notifikasi', [
            'id',
            'pengguna_id',
            'judul',
            'pesan',
            'tautan',
            'dibaca',
            'dibaca_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_notifikasi_can_be_created_with_uuid_primary_key(): void
    {
        $notifikasi = Notifikasi::factory()->create();

        $this->assertIsString($notifikasi->id);
        $this->assertSame(36, strlen($notifikasi->id));
        $this->assertFalse($notifikasi->dibaca);
    }

    public function test_pengguna_id_fk_restricts_deletion_of_pengguna_still_referenced(): void
    {
        $notifikasi = Notifikasi::factory()->create();

        $this->expectException(QueryException::class);

        Pengguna::query()->whereKey($notifikasi->pengguna_id)->forceDelete();
    }
}
