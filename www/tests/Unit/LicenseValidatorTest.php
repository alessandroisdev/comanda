<?php

namespace Tests\Unit;

use App\Enums\LicenseStatusEnum;
use App\Services\Licensing\LicenseValidator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseValidatorTest extends TestCase
{
    private LicenseValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LicenseValidator;
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
            'signature' => 'some-signature',
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
            'status' => 'active',
        ];

        $privateKeyPath = storage_path('app/keys/license_private.key');
        if (file_exists($privateKeyPath)) {
            $privateKey = file_get_contents($privateKeyPath);
            $privKeyResource = openssl_pkey_get_private($privateKey);
            ksort($licenseData);
            $canonicalData = json_encode($licenseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $signature = '';
            openssl_sign($canonicalData, $signature, $privKeyResource, OPENSSL_ALGO_SHA256);
            $licenseData['signature'] = base64_encode($signature);
            if ($privKeyResource) {
                openssl_free_key($privKeyResource);
            }
        } else {
            $licenseData['signature'] = 'mocked-signature';
        }

        $status = $this->validator->validate($licenseData);
        $this->assertEquals(LicenseStatusEnum::EXPIRED, $status);
    }
}
