<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Licensing\LicenseValidator;
use App\Enums\LicenseStatusEnum;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class LicenseValidatorTest extends TestCase
{
    private LicenseValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LicenseValidator();
    }

    #[Test]
    public function it_returns_invalid_if_signature_is_missing()
    {
        $licenseData = [
            'installation_uuid' => $this->validator->getLocalInstallationUuid(),
            'expires_at' => Carbon::now()->addYear()->toIso8601String(),
        ];

        $status = $this->validator->validate($licenseData);
        $this->assertEquals(LicenseStatusEnum::INVALID, $status);
    }

    #[Test]
    public function it_returns_invalid_if_installation_uuid_does_not_match()
    {
        $licenseData = [
            'installation_uuid' => 'different-uuid-123',
            'expires_at' => Carbon::now()->addYear()->toIso8601String(),
            'signature' => 'some-signature'
        ];

        $status = $this->validator->validate($licenseData);
        $this->assertEquals(LicenseStatusEnum::INVALID, $status);
    }

    #[Test]
    public function it_returns_expired_if_license_date_is_in_the_past()
    {
        $localUuid = $this->validator->getLocalInstallationUuid();
        
        $licenseData = [
            'installation_uuid' => $localUuid,
            'expires_at' => Carbon::now()->subDay()->toIso8601String(),
            'signature' => 'mocked-signature',
            'status' => 'active'
        ];

        $status = $this->validator->validate($licenseData);
        $this->assertEquals(LicenseStatusEnum::EXPIRED, $status);
    }
}
