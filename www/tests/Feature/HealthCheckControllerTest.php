<?php

namespace Tests\Feature;

use App\Enums\LicenseStatusEnum;
use App\Services\Licensing\LicenseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthCheckControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mockar o LicenseManager para que a licença sempre retorne ativa no healthcheck de teste
        $licenseMock = $this->createMock(LicenseManager::class);
        $licenseMock->method('getStatus')->willReturn(LicenseStatusEnum::ACTIVE);
        $this->app->instance(LicenseManager::class, $licenseMock);

        // Mockar a conexão Redis usando Mockery para suportar Redis::connection()->ping()
        $redisConnectionMock = \Mockery::mock(Connection::class);
        $redisConnectionMock->shouldReceive('ping')->andReturn('PONG');

        Redis::shouldReceive('connection')->andReturn($redisConnectionMock);
    }

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
