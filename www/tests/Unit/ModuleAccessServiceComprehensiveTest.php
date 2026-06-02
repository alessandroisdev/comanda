<?php

namespace Tests\Unit;

use App\Enums\LicenseStatusEnum;
use App\Services\Licensing\LicenseManager;
use App\Services\Licensing\ModuleAccessService;
use Tests\TestCase;

class ModuleAccessServiceComprehensiveTest extends TestCase
{
    private ModuleAccessService $service;

    private $licenseManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->licenseManagerMock = $this->createMock(LicenseManager::class);
        $this->service = new ModuleAccessService($this->licenseManagerMock);
    }

    private function setLicenseMock(LicenseStatusEnum $status, array $modules = [])
    {
        $this->licenseManagerMock->method('getStatus')->willReturn($status);
        $this->licenseManagerMock->method('getActiveLicense')->willReturn([
            'id' => 1,
            'installation_uuid' => 'local-uuid',
            'modules' => $modules,
            'expires_at' => now()->addYear()->toIso8601String(),
            'status' => $status->value,
        ]);
    }

    // --- Active & Licensed ---
    public function test_pdv_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['pdv', 'printing']);
        $this->assertTrue($this->service->hasAccess('pdv'));
    }

    public function test_waiter_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['waiter']);
        $this->assertTrue($this->service->hasAccess('waiter'));
    }

    public function test_kitchen_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['kitchen', 'printing']);
        $this->assertTrue($this->service->hasAccess('kitchen'));
    }

    public function test_hall_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['hall']);
        $this->assertTrue($this->service->hasAccess('hall'));
    }

    public function test_delivery_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['delivery']);
        $this->assertTrue($this->service->hasAccess('delivery'));
    }

    public function test_tablet_table_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['tablet_table']);
        $this->assertTrue($this->service->hasAccess('tablet_table'));
    }

    public function test_kiosk_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['kiosk', 'printing']);
        $this->assertTrue($this->service->hasAccess('kiosk'));
    }

    public function test_digital_menu_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['digital_menu']);
        $this->assertTrue($this->service->hasAccess('digital_menu'));
    }

    public function test_printing_active_and_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['printing']);
        $this->assertTrue($this->service->hasAccess('printing'));
    }

    // --- Active but NOT licensed ---
    public function test_pdv_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('pdv'));
    }

    public function test_waiter_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('waiter'));
    }

    public function test_kitchen_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('kitchen'));
    }

    public function test_hall_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('hall'));
    }

    public function test_delivery_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('delivery'));
    }

    public function test_tablet_table_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('tablet_table'));
    }

    public function test_kiosk_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('kiosk'));
    }

    public function test_digital_menu_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('digital_menu'));
    }

    public function test_printing_active_but_not_licensed()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, []);
        $this->assertFalse($this->service->hasAccess('printing'));
    }

    // --- Expired ---
    public function test_pdv_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['pdv', 'printing']);
        $this->assertFalse($this->service->hasAccess('pdv'));
    }

    public function test_waiter_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['waiter']);
        $this->assertFalse($this->service->hasAccess('waiter'));
    }

    public function test_kitchen_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['kitchen', 'printing']);
        $this->assertFalse($this->service->hasAccess('kitchen'));
    }

    public function test_hall_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['hall']);
        $this->assertFalse($this->service->hasAccess('hall'));
    }

    public function test_delivery_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['delivery']);
        $this->assertFalse($this->service->hasAccess('delivery'));
    }

    public function test_tablet_table_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['tablet_table']);
        $this->assertFalse($this->service->hasAccess('tablet_table'));
    }

    public function test_kiosk_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['kiosk', 'printing']);
        $this->assertFalse($this->service->hasAccess('kiosk'));
    }

    public function test_digital_menu_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['digital_menu']);
        $this->assertFalse($this->service->hasAccess('digital_menu'));
    }

    public function test_printing_when_license_expired()
    {
        $this->setLicenseMock(LicenseStatusEnum::EXPIRED, ['printing']);
        $this->assertFalse($this->service->hasAccess('printing'));
    }

    // --- Suspended ---
    public function test_pdv_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['pdv', 'printing']);
        $this->assertFalse($this->service->hasAccess('pdv'));
    }

    public function test_waiter_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['waiter']);
        $this->assertFalse($this->service->hasAccess('waiter'));
    }

    public function test_kitchen_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['kitchen', 'printing']);
        $this->assertFalse($this->service->hasAccess('kitchen'));
    }

    public function test_hall_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['hall']);
        $this->assertFalse($this->service->hasAccess('hall'));
    }

    public function test_delivery_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['delivery']);
        $this->assertFalse($this->service->hasAccess('delivery'));
    }

    public function test_tablet_table_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['tablet_table']);
        $this->assertFalse($this->service->hasAccess('tablet_table'));
    }

    public function test_kiosk_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['kiosk', 'printing']);
        $this->assertFalse($this->service->hasAccess('kiosk'));
    }

    public function test_digital_menu_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['digital_menu']);
        $this->assertFalse($this->service->hasAccess('digital_menu'));
    }

    public function test_printing_when_license_suspended()
    {
        $this->setLicenseMock(LicenseStatusEnum::SUSPENDED, ['printing']);
        $this->assertFalse($this->service->hasAccess('printing'));
    }

    // --- Revoked ---
    public function test_pdv_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['pdv', 'printing']);
        $this->assertFalse($this->service->hasAccess('pdv'));
    }

    public function test_waiter_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['waiter']);
        $this->assertFalse($this->service->hasAccess('waiter'));
    }

    public function test_kitchen_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['kitchen', 'printing']);
        $this->assertFalse($this->service->hasAccess('kitchen'));
    }

    public function test_hall_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['hall']);
        $this->assertFalse($this->service->hasAccess('hall'));
    }

    public function test_delivery_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['delivery']);
        $this->assertFalse($this->service->hasAccess('delivery'));
    }

    public function test_tablet_table_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['tablet_table']);
        $this->assertFalse($this->service->hasAccess('tablet_table'));
    }

    public function test_kiosk_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['kiosk', 'printing']);
        $this->assertFalse($this->service->hasAccess('kiosk'));
    }

    public function test_digital_menu_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['digital_menu']);
        $this->assertFalse($this->service->hasAccess('digital_menu'));
    }

    public function test_printing_when_license_revoked()
    {
        $this->setLicenseMock(LicenseStatusEnum::REVOKED, ['printing']);
        $this->assertFalse($this->service->hasAccess('printing'));
    }

    // --- Dependencies ---
    public function test_pdv_denied_if_printing_missing()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['pdv']);
        $this->assertFalse($this->service->hasAccess('pdv'));
    }

    public function test_kitchen_denied_if_printing_missing()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['kitchen']);
        $this->assertFalse($this->service->hasAccess('kitchen'));
    }

    public function test_kiosk_denied_if_printing_missing()
    {
        $this->setLicenseMock(LicenseStatusEnum::ACTIVE, ['kiosk']);
        $this->assertFalse($this->service->hasAccess('kiosk'));
    }
}
