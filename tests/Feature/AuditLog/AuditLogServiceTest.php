<?php

namespace Tests\Feature\AuditLog;

use App\Models\AuditLog;
use App\Models\Pengguna;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AuditLogService::class);
    }

    public function test_catat_creates_a_row_with_given_fields(): void
    {
        $pengguna = Pengguna::factory()->create();

        $log = $this->service->catat(
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
        $log = $this->service->catat(
            pengguna: null,
            aksi: 'AKSES_DITOLAK',
        );

        $this->assertDatabaseHas('audit_log', [
            'id' => $log->id,
            'pengguna_id' => null,
        ]);
    }

    public function test_list_mengembalikan_terbaru_lebih_dulu(): void
    {
        $lama = AuditLog::factory()->create(['created_at' => now()->subDays(2)]);
        $baru = AuditLog::factory()->create(['created_at' => now()]);

        $hasil = $this->service->list();

        $this->assertSame($baru->id, $hasil->items()[0]->id);
        $this->assertSame($lama->id, $hasil->items()[1]->id);
    }

    public function test_list_filter_aksi_partial_match(): void
    {
        AuditLog::factory()->create(['aksi' => 'AKSES_DITOLAK']);
        AuditLog::factory()->create(['aksi' => 'LAPORAN_DRILL_DOWN_INDIVIDUAL']);

        $hasil = $this->service->list(['aksi' => 'DITOLAK']);

        $this->assertCount(1, $hasil->items());
        $this->assertSame('AKSES_DITOLAK', $hasil->items()[0]->aksi);
    }

    public function test_list_filter_rentang_tanggal(): void
    {
        AuditLog::factory()->create(['created_at' => now()->subDays(10)]);
        $dalamRentang = AuditLog::factory()->create(['created_at' => now()->subDays(1)]);

        $hasil = $this->service->list(['dari' => now()->subDays(3)->toDateString(), 'sampai' => now()->toDateString()]);

        $this->assertCount(1, $hasil->items());
        $this->assertSame($dalamRentang->id, $hasil->items()[0]->id);
    }

    public function test_list_cursor_pagination_lanjut_ke_halaman_berikutnya(): void
    {
        AuditLog::factory()->count(5)->sequence(fn ($sequence) => ['created_at' => now()->subMinutes($sequence->index)])->create();

        $halamanPertama = $this->service->list([], null, 2);
        $this->assertCount(2, $halamanPertama->items());
        $this->assertNotNull($halamanPertama->nextCursor());

        $halamanKedua = $this->service->list([], $halamanPertama->nextCursor()->encode(), 2);
        $this->assertCount(2, $halamanKedua->items());

        $idHalamanPertama = collect($halamanPertama->items())->pluck('id');
        $idHalamanKedua = collect($halamanKedua->items())->pluck('id');
        $this->assertEmpty($idHalamanPertama->intersect($idHalamanKedua));
    }
}
