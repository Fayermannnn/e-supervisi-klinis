<?php

namespace Tests\Feature\Pengguna;

use App\Models\Pengguna;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenggunaMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('pengguna', [
            'id',
            'sekolah_id',
            'nama',
            'email',
            'password',
            'nip_nuptk',
            'no_telepon',
            'status_aktif',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_pengguna_can_be_created_without_sekolah(): void
    {
        $pengguna = Pengguna::factory()->create();

        $this->assertNull($pengguna->sekolah_id);
    }

    public function test_pengguna_belongs_to_sekolah(): void
    {
        $sekolah = Sekolah::factory()->create();
        $pengguna = Pengguna::factory()->create(['sekolah_id' => $sekolah->id]);

        $this->assertTrue($pengguna->sekolah->is($sekolah));
    }

    public function test_sekolah_id_is_set_null_when_sekolah_is_hard_deleted(): void
    {
        $sekolah = Sekolah::factory()->create();
        $pengguna = Pengguna::factory()->create(['sekolah_id' => $sekolah->id]);

        $sekolah->forceDelete();

        $this->assertNull($pengguna->fresh()->sekolah_id);
    }

    public function test_pengguna_can_be_created_with_uuid_primary_key(): void
    {
        $pengguna = Pengguna::factory()->create();

        $this->assertIsString($pengguna->id);
        $this->assertSame(36, strlen($pengguna->id));
        $this->assertTrue($pengguna->status_aktif);
    }

    public function test_pengguna_password_is_hidden_from_array(): void
    {
        $pengguna = Pengguna::factory()->create();

        $this->assertArrayNotHasKey('password', $pengguna->toArray());
    }

    public function test_pengguna_soft_deletes_instead_of_hard_deleting(): void
    {
        $pengguna = Pengguna::factory()->create();

        $pengguna->delete();

        $this->assertSoftDeleted('pengguna', ['id' => $pengguna->id]);
    }
}
