<?php

namespace Tests\Feature\AuditLog;

use App\Livewire\AuditLog\LogAudit;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class LogAuditScreenTest extends TestCase
{
    use InteractsWithRoles, RefreshDatabase;

    public function test_log_audit_requires_authentication(): void
    {
        $this->get('/audit-log')->assertRedirect(route('login'));
    }

    public function test_admin_dinas_tidak_bisa_membuka_log_audit(): void
    {
        $this->actingAs($this->penggunaWithRole('admin_dinas'), 'web');

        Livewire::test(LogAudit::class)->assertForbidden();
    }

    public function test_super_admin_bisa_melihat_daftar_log(): void
    {
        $this->actingAs($this->penggunaWithRole('super_admin'), 'web');
        AuditLog::factory()->create(['aksi' => 'AKSES_DITOLAK']);

        Livewire::test(LogAudit::class)
            ->assertSee('AKSES_DITOLAK');
    }

    public function test_filter_aksi_mempersempit_hasil(): void
    {
        $this->actingAs($this->penggunaWithRole('super_admin'), 'web');
        AuditLog::factory()->create(['aksi' => 'AKSES_DITOLAK']);
        AuditLog::factory()->create(['aksi' => 'LAPORAN_DRILL_DOWN_INDIVIDUAL']);

        Livewire::test(LogAudit::class)
            ->set('aksi', 'DRILL_DOWN')
            ->assertSee('LAPORAN_DRILL_DOWN_INDIVIDUAL')
            ->assertDontSee('AKSES_DITOLAK');
    }
}
