<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthCheckControllerTest extends TestCase
{
    #[Test]
    public function it_returns_successful_liveness_probe()
    {
        $response = $this->getJson('/liveness');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'alive',
            ]);
    }

    #[Test]
    public function it_returns_successful_readiness_probe()
    {
        $response = $this->getJson('/readiness');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'ready',
            ]);
    }

    #[Test]
    public function it_returns_complete_health_check_payload()
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'timestamp',
                'services' => [
                    'database',
                    'redis',
                    'license',
                    'storage',
                ],
            ]);
    }
}
