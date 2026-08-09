<?php

namespace Tests\Feature\TindakLanjut;

use App\Exceptions\RtlRequiredBeforeCloseException;
use App\Models\RencanaTindakLanjut;
use App\Models\SesiSupervisi;
use App\Modules\TindakLanjut\Services\RtlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtlServiceTest extends TestCase
{
    use RefreshDatabase;

    private RtlService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RtlService::class);
    }

    public function test_isi_rtl_membuat_rtl_baru(): void
    {
        $sesi = SesiSupervisi::factory()->create(['status' => 'umpan_balik']);

        $rtl = $this->service->isiRtl($sesi->id, [
            'deskripsi' => 'Ikuti pelatihan manajemen kelas',
            'target_waktu' => now()->addWeek()->toDateString(),
            'kategori' => 'pedagogik',
        ]);

        $this->assertSame('Ikuti pelatihan manajemen kelas', $rtl->deskripsi);
        $this->assertSame('belum', $rtl->status);
        $this->assertSame('rtl', $sesi->fresh()->status);
    }

    public function test_isi_rtl_kedua_kali_memperbarui_bukan_menduplikasi(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $this->service->isiRtl($sesi->id, [
            'deskripsi' => 'Deskripsi lama',
            'target_waktu' => now()->addWeek()->toDateString(),
        ]);

        $rtl = $this->service->isiRtl($sesi->id, [
            'deskripsi' => 'Deskripsi baru',
            'target_waktu' => now()->addWeeks(2)->toDateString(),
        ]);

        $this->assertSame(1, RencanaTindakLanjut::where('sesi_id', $sesi->id)->count());
        $this->assertSame('Deskripsi baru', $rtl->deskripsi);
    }

    public function test_isi_rtl_mempertahankan_tanggal_diisi_saat_diperbarui(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $pertama = $this->service->isiRtl($sesi->id, [
            'deskripsi' => 'Awal',
            'target_waktu' => now()->addWeek()->toDateString(),
        ]);

        $kedua = $this->service->isiRtl($sesi->id, [
            'deskripsi' => 'Revisi',
            'target_waktu' => now()->addWeek()->toDateString(),
        ]);

        $this->assertEquals($pertama->tanggal_diisi, $kedua->tanggal_diisi);
    }

    /**
     * TC-RTL-001 (BR-04): sesi TIDAK bisa ditandai selesai tanpa RTL -
     * ini penegakan langsung bottleneck B-04.
     */
    public function test_tandai_selesai_menolak_jika_rtl_kosong(): void
    {
        $sesi = SesiSupervisi::factory()->create();

        $this->expectException(RtlRequiredBeforeCloseException::class);

        $this->service->tandaiSelesai($sesi->id);
    }

    public function test_tandai_selesai_berhasil_jika_rtl_sudah_diisi(): void
    {
        $sesi = SesiSupervisi::factory()->create();
        $this->service->isiRtl($sesi->id, [
            'deskripsi' => 'Rencana tindak lanjut',
            'target_waktu' => now()->addWeek()->toDateString(),
        ]);

        $updated = $this->service->tandaiSelesai($sesi->id);

        $this->assertSame('selesai', $updated->status);
    }
}
