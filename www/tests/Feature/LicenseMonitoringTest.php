<?php

namespace Tests\Feature;

use App\Enums\LicenseStatusEnum;
use App\Services\Licensing\LicenseManager;
use App\Services\Licensing\LicenseValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LicenseMonitoringTest extends TestCase
{
    private string $licensePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->licensePath = storage_path('app/license.json');
        @unlink($this->licensePath);
        Cache::forget('license:active_metadata');
        Carbon::setTestNow('2026-06-01 12:00:00');
    }

    protected function tearDown(): void
    {
        @unlink($this->licensePath);
        Cache::forget('license:active_metadata');
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function writeMockLicense(array $data)
    {
        File::put($this->licensePath, json_encode($data));
        Cache::forget('license:active_metadata');
    }

    public function test_get_days_until_expiration_returns_correct_count()
    {
        $manager = app(LicenseManager::class);
        
        $this->assertNull($manager->getDaysUntilExpiration());

        $expiresAt = Carbon::now()->addDays(10);
        $this->writeMockLicense([
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        $this->assertEquals(10, $manager->getDaysUntilExpiration());
    }

    public function test_is_expiring_soon_detects_under_15_days()
    {
        $manager = app(LicenseManager::class);
        
        $this->assertFalse($manager->isExpiringSoon());

        // Caso 1: 20 dias restantes (não está prestes a expirar)
        $this->writeMockLicense([
            'expires_at' => Carbon::now()->addDays(20)->toIso8601String(),
        ]);
        $this->assertFalse($manager->isExpiringSoon());

        // Caso 2: 5 dias restantes (está prestes a expirar)
        $this->writeMockLicense([
            'expires_at' => Carbon::now()->addDays(5)->toIso8601String(),
        ]);
        $this->assertTrue($manager->isExpiringSoon());
    }

    public function test_is_operating_in_grace_period_detects_within_7_days_of_expiration()
    {
        $manager = app(LicenseManager::class);
        
        $this->assertFalse($manager->isOperatingInGracePeriod());

        // Caso 1: Venceu há 3 dias (dentro dos 7 dias do grace period)
        $this->writeMockLicense([
            'expires_at' => Carbon::now()->subDays(3)->toIso8601String(),
        ]);
        $this->assertTrue($manager->isOperatingInGracePeriod());

        // Caso 2: Venceu há 10 dias (carência estourada)
        $this->writeMockLicense([
            'expires_at' => Carbon::now()->subDays(10)->toIso8601String(),
        ]);
        $this->assertFalse($manager->isOperatingInGracePeriod());
    }

    public function test_get_license_alert_returns_appropriate_danger_warning_or_info()
    {
        $validatorMock = $this->createMock(LicenseValidator::class);
        
        // Mockaremos o validador para retornar ACTIVE por padrão
        $validatorMock->method('validate')->willReturn(LicenseStatusEnum::ACTIVE);
        
        $manager = new LicenseManager($validatorMock);

        // Sem licença
        $alert = $manager->getLicenseAlert();
        $this->assertEquals('danger', $alert['type']);
        $this->assertStringContainsString('Nenhuma licença comercial encontrada', $alert['message']);

        // Licença prestes a expirar (10 dias)
        $this->writeMockLicense([
            'expires_at' => Carbon::now()->addDays(10)->toIso8601String(),
        ]);
        $alert = $manager->getLicenseAlert();
        $this->assertEquals('info', $alert['type']);
        $this->assertStringContainsString('irá expirar em 10 dias', $alert['message']);

        // Licença expirada operando sob Grace Period
        // Para simular expirada no status, o validador retorna EXPIRED
        $validatorMock2 = $this->createMock(LicenseValidator::class);
        $validatorMock2->method('validate')->willReturn(LicenseStatusEnum::EXPIRED);
        $manager2 = new LicenseManager($validatorMock2);

        $this->writeMockLicense([
            'expires_at' => Carbon::now()->subDays(2)->toIso8601String(),
        ]);
        $alert2 = $manager2->getLicenseAlert();
        $this->assertEquals('warning', $alert2['type']);
        $this->assertStringContainsString('período de carência', $alert2['message']);
    }
}

