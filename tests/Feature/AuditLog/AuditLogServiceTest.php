<?php

namespace Tests\Feature\AuditLog;

use App\Models\Pengguna;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catat_creates_a_row_with_given_fields(): void
    {
        $pengguna = Pengguna::factory()->create();
        $service = app(AuditLogService::class);

        $log = $service->catat(
            pengguna: $pengguna,
            aksi: 'AKSES_DITOLAK',
            deskripsi: 'GET /sekolah',
            ipAddress: '127.0.0.1',
            correlationId: '11111111-1111-1111-1111-111111111111',
        );

        $this->assertDatabaseHas('audit_log', [
            'id' => $log->id,
            'pengguna_id' => $pengguna->id,
            'aksi' => 'AKSES_DITOLAK',
            'deskripsi' => 'GET /sekolah',
        ]);
    }

    public function test_catat_allows_null_pengguna_for_unauthenticated_actor(): void
    {
        $service = app(AuditLogService::class);

        $log = $service->catat(pengguna: null, aksi: 'AKSES_DITOLAK');

        $this->assertNull($log->pengguna_id);
    }
}
