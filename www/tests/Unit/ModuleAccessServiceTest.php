<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Licensing\ModuleAccessService;
use App\Services\Licensing\LicenseManager;
use App\Enums\LicenseStatusEnum;
use PHPUnit\Framework\Attributes\Test;

class ModuleAccessServiceTest extends TestCase
{
    private ModuleAccessService $service;
    private $licenseManagerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->licenseManagerMock = $this->createMock(LicenseManager::class);
        $this->service = new ModuleAccessService($this->licenseManagerMock);
    }

    #[Test]
    public function it_always_allows_access_to_core_modules()
    {
        $this->assertTrue($this->service->hasAccess('admin'));
        $this->assertTrue($this->service->hasAccess('api'));
        $this->assertTrue($this->service->hasAccess('licensing'));
    }

    #[Test]
    public function it_denies_access_to_commercial_modules_if_license_is_inactive()
    {
        $this->licenseManagerMock->method('getStatus')
            ->willReturn(LicenseStatusEnum::EXPIRED);

        $this->assertFalse($this->service->hasAccess('pdv'));
        $this->assertFalse($this->service->hasAccess('waiter'));
    }
}
