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

    private ?string $backupKeyContent = null;

    private string $publicKeyPath;

    private string $testPrivateKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LicenseValidator;
        $this->publicKeyPath = storage_path('app/keys/license_public.key');

        // Backup existing public key
        if (file_exists($this->publicKeyPath)) {
            $this->backupKeyContent = file_get_contents($this->publicKeyPath);
        } else {
            $dir = dirname($this->publicKeyPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Generate temporary RSA-2048 keypair
        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($res, $privateKey);
        $this->testPrivateKey = $privateKey;
        $publicKeyDetails = openssl_pkey_get_details($res);
        $testPublicKey = $publicKeyDetails['key'];

        file_put_contents($this->publicKeyPath, $testPublicKey);
    }

    protected function tearDown(): void
    {
        if ($this->backupKeyContent !== null) {
            file_put_contents($this->publicKeyPath, $this->backupKeyContent);
        } else {
            if (file_exists($this->publicKeyPath)) {
                unlink($this->publicKeyPath);
            }
        }
        parent::tearDown();
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

        $privKeyResource = openssl_pkey_get_private($this->testPrivateKey);
        ksort($licenseData);
        $canonicalData = json_encode($licenseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = '';
        openssl_sign($canonicalData, $signature, $privKeyResource, OPENSSL_ALGO_SHA256);
        $licenseData['signature'] = base64_encode($signature);
        if ($privKeyResource) {
            openssl_free_key($privKeyResource);
        }

        $status = $this->validator->validate($licenseData);
        $this->assertEquals(LicenseStatusEnum::EXPIRED, $status);
    }

    #[Test]
    public function it_returns_active_if_license_is_valid()
    {
        $localUuid = $this->validator->getLocalInstallationUuid();

        $licenseData = [
            'installation_uuid' => $localUuid,
            'expires_at' => Carbon::now()->addYear()->toIso8601String(),
            'status' => 'active',
        ];

        $privKeyResource = openssl_pkey_get_private($this->testPrivateKey);
        ksort($licenseData);
        $canonicalData = json_encode($licenseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = '';
        openssl_sign($canonicalData, $signature, $privKeyResource, OPENSSL_ALGO_SHA256);
        $licenseData['signature'] = base64_encode($signature);
        if ($privKeyResource) {
            openssl_free_key($privKeyResource);
        }

        $status = $this->validator->validate($licenseData);
        $this->assertEquals(LicenseStatusEnum::ACTIVE, $status);
    }
}
