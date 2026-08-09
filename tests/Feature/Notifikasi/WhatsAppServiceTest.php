<?php

namespace Tests\Feature\Notifikasi;

use App\Modules\Notifikasi\Services\WhatsAppService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Sprint 10: WhatsAppService sengaja MELEMPAR exception saat API gagal
 * (bukan menelannya) - lihat doc-block class-nya. Retry policy job
 * (KirimNotifikasiWhatsAppJobTest) yang menangani "kegagalan tidak
 * permanen", bukan service ini.
 */
class WhatsAppServiceTest extends TestCase
{
    public function test_kirim_berhasil_saat_api_merespons_sukses(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);
        config(['services.fonnte.url' => 'https://api.fonnte.com/send', 'services.fonnte.token' => 'dummy-token']);

        app(WhatsAppService::class)->kirim('081234567890', 'Pesan uji coba');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.fonnte.com/send'
            && $request['target'] === '081234567890'
            && $request['message'] === 'Pesan uji coba'
            && $request->hasHeader('Authorization', 'dummy-token'));
    }

    public function test_kirim_melempar_exception_saat_api_gagal(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false], 500)]);
        config(['services.fonnte.url' => 'https://api.fonnte.com/send']);

        $this->expectException(RequestException::class);

        app(WhatsAppService::class)->kirim('081234567890', 'Pesan uji coba');
    }
}
