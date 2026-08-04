<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    public function test_the_application_boots_and_responds(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_health_check_endpoint_responds(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
