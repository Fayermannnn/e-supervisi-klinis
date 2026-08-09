<?php

namespace Tests\Feature\Notifikasi;

use App\Jobs\KirimNotifikasiWhatsAppJob;
use App\Modules\Notifikasi\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KirimNotifikasiWhatsAppJobTest extends TestCase
{
    public function test_job_punya_retry_policy_3x_dengan_backoff(): void
    {
        $job = new KirimNotifikasiWhatsAppJob('081234567890', 'Pesan');

        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300, 900], $job->backoff);
    }

    public function test_job_memanggil_whatsapp_service_dengan_parameter_benar(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);
        config(['services.fonnte.url' => 'https://api.fonnte.com/send']);

        (new KirimNotifikasiWhatsAppJob('081234567890', 'RTL tertunda'))
            ->handle(app(WhatsAppService::class));

        Http::assertSent(fn ($request) => $request['target'] === '081234567890' && $request['message'] === 'RTL tertunda');
    }
}
