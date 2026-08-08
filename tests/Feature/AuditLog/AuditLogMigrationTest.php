<?php

namespace Tests\Feature\AuditLog;

use App\Models\AuditLog;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('audit_log', [
            'id',
            'pengguna_id',
            'aksi',
            'deskripsi',
            'model_type',
            'model_id',
            'ip_address',
            'correlation_id',
            'created_at',
        ]));
    }

    public function test_audit_log_table_has_no_updated_at_or_soft_delete_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('audit_log', 'updated_at'));
        $this->assertFalse(Schema::hasColumn('audit_log', 'deleted_at'));
    }

    public function test_audit_log_can_be_created_with_uuid_primary_key(): void
    {
        $log = AuditLog::create(['aksi' => 'TEST_AKSI']);

        $this->assertIsString($log->id);
        $this->assertSame(36, strlen($log->id));
    }

    public function test_audit_log_belongs_to_pengguna_and_is_set_null_when_pengguna_is_deleted(): void
    {
        $pengguna = Pengguna::factory()->create();
        $log = AuditLog::create(['pengguna_id' => $pengguna->id, 'aksi' => 'TEST_AKSI']);

        $this->assertTrue($log->pengguna->is($pengguna));

        $pengguna->forceDelete();

        $this->assertNull($log->fresh()->pengguna_id);
    }
}
