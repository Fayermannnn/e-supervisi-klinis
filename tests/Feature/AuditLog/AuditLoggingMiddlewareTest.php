<?php

namespace Tests\Feature\AuditLog;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class AuditLoggingMiddlewareTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_a_403_response_is_recorded_to_audit_log(): void
    {
        $pengguna = $this->penggunaWithRole('guru');
        $this->actingAs($pengguna, 'web');

        $this->get('/sekolah')->assertForbidden();

        $this->assertDatabaseHas('audit_log', [
            'pengguna_id' => $pengguna->id,
            'aksi' => 'AKSES_DITOLAK',
            'deskripsi' => 'GET sekolah',
        ]);
    }

    public function test_a_successful_response_is_not_recorded_to_audit_log(): void
    {
        $pengguna = $this->penggunaWithRole('admin_dinas');
        $this->actingAs($pengguna, 'web');

        $this->get('/sekolah')->assertOk();

        $this->assertSame(0, AuditLog::count());
    }

    public function test_audit_log_row_carries_the_request_correlation_id(): void
    {
        $pengguna = $this->penggunaWithRole('guru');
        $this->actingAs($pengguna, 'web');

        $this->withHeaders(['X-Correlation-Id' => '33333333-3333-3333-3333-333333333333'])
            ->get('/sekolah')
            ->assertForbidden();

        $this->assertDatabaseHas('audit_log', [
            'correlation_id' => '33333333-3333-3333-3333-333333333333',
        ]);
    }
}
