<?php

namespace Tests\Feature\Notifikasi;

use App\Models\Notifikasi;
use App\Models\Pengguna;
use App\Modules\Notifikasi\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    public function test_kirim_menyimpan_notifikasi_untuk_penerima(): void
    {
        $penerima = Pengguna::factory()->create();

        $notifikasi = $this->service->kirim($penerima, 'Judul', 'Isi pesan', '/tautan');

        $this->assertDatabaseHas('notifikasi', [
            'id' => $notifikasi->id,
            'pengguna_id' => $penerima->id,
            'judul' => 'Judul',
            'dibaca' => false,
        ]);
    }

    public function test_jumlah_belum_dibaca_menghitung_hanya_milik_pengguna_tsb(): void
    {
        $pengguna = Pengguna::factory()->create();
        $lainPengguna = Pengguna::factory()->create();

        Notifikasi::factory()->count(3)->create(['pengguna_id' => $pengguna->id, 'dibaca' => false]);
        Notifikasi::factory()->create(['pengguna_id' => $pengguna->id, 'dibaca' => true]);
        Notifikasi::factory()->count(2)->create(['pengguna_id' => $lainPengguna->id, 'dibaca' => false]);

        $this->assertSame(3, $this->service->jumlahBelumDibaca($pengguna));
    }

    public function test_tandai_dibaca_menandai_notifikasi_sebagai_dibaca(): void
    {
        $notifikasi = Notifikasi::factory()->create(['dibaca' => false]);

        $this->service->tandaiDibaca($notifikasi);

        $this->assertDatabaseHas('notifikasi', ['id' => $notifikasi->id, 'dibaca' => true]);
        $this->assertNotNull($notifikasi->fresh()->dibaca_at);
    }
}
