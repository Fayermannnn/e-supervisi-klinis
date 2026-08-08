<?php

namespace Tests\Feature\AuditLog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrelationIdMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_includes_a_generated_correlation_id_header(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Correlation-Id');
        $this->assertNotEmpty($response->headers->get('X-Correlation-Id'));
    }

    public function test_client_provided_correlation_id_is_echoed_back(): void
    {
        $response = $this->withHeaders(['X-Correlation-Id' => '22222222-2222-2222-2222-222222222222'])
            ->get('/login');

        $response->assertHeader('X-Correlation-Id', '22222222-2222-2222-2222-222222222222');
    }
}
