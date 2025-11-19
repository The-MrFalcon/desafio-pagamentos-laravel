<?php

namespace Tests\Feature;

use Tests\TestCase;

class PixFlowTest extends TestCase
{
    public function test_health_ok()
    {
        $response = $this->get('/api/health');
        $response->assertStatus(200);
    }
}
