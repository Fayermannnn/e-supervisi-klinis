<?php

namespace Tests\Feature\AuditLog;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_guest_cannot_access_audit_log_endpoint(): void
    {
        $this->getJson('/api/v1/audit-log')->assertStatus(401);
    }

    public function test_admin_dinas_tidak_bisa_mengakses_log_audit(): void
    {
        $admin = $this->penggunaWithRole('admin_dinas');
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/audit-log')->assertStatus(403);
    }

    public function test_guru_tidak_bisa_mengakses_log_audit(): void
    {
        $guru = $this->penggunaWithRole('guru');
        Sanctum::actingAs($guru);

        $this->getJson('/api/v1/audit-log')->assertStatus(403);
    }

    public function test_super_admin_bisa_melihat_log_audit_dengan_cursor_pagination(): void
    {
        $superAdmin = $this->penggunaWithRole('super_admin');
        AuditLog::factory()->count(3)->create();
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/audit-log?'.http_build_query(['cursor' => null]));

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['next_cursor', 'prev_cursor'], 'errors']);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_super_admin_bisa_filter_berdasarkan_aksi(): void
    {
        $superAdmin = $this->penggunaWithRole('super_admin');
        AuditLog::factory()->create(['aksi' => 'AKSES_DITOLAK']);
        AuditLog::factory()->create(['aksi' => 'LAPORAN_DRILL_DOWN_INDIVIDUAL']);
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/audit-log?aksi=DITOLAK');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
