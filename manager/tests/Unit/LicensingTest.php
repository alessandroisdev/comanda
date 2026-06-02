<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\License;
use App\Services\Licensing\KeyGeneratorService;
use App\Services\Licensing\LicenseIssuerService;
use Carbon\Carbon;
use Tests\TestCase;

class LicensingTest extends TestCase
{
    private KeyGeneratorService $keyGenerator;
    private LicenseIssuerService $licenseIssuer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keyGenerator = new KeyGeneratorService();
        $this->licenseIssuer = new LicenseIssuerService($this->keyGenerator);
    }

    public function test_it_can_generate_rsa_2048_keypair(): void
    {
        $keys = $this->keyGenerator->generate(true);

        $this->assertNotEmpty($keys['private_key']);
        $this->assertNotEmpty($keys['public_key']);
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', $keys['private_key']);
        $this->assertStringContainsString('-----BEGIN PUBLIC KEY-----', $keys['public_key']);
    }

    public function test_it_can_issue_and_sign_a_license(): void
    {
        $this->keyGenerator->generate(true);

        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Alessandro Test',
            'client_email' => 'test@client.com',
            'client_document' => '12345678901',
            'plan_name' => 'Premium',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $modules = ['pdv', 'delivery', 'kitchen'];
        $installationUuid = (string) \Illuminate\Support\Str::uuid();

        $activationKey = $this->licenseIssuer->issue($license, $modules, $installationUuid);

        $this->assertNotEmpty($activationKey);

        $decoded = json_decode(base64_decode($activationKey), true);
        $this->assertIsArray($decoded);
        $this->assertEquals($license->uuid, $decoded['license_uuid']);
        $this->assertEquals($installationUuid, $decoded['installation_uuid']);
        $this->assertNotEmpty($decoded['signature']);

        // Valida a assinatura digital usando OpenSSL com a chave pública local
        $signature = base64_decode($decoded['signature']);
        unset($decoded['signature']);
        ksort($decoded);
        $canonicalJson = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $publicKey = $this->keyGenerator->getPublicKey();
        $pubKeyResource = openssl_pkey_get_public($publicKey);
        
        $result = openssl_verify($canonicalJson, $signature, $pubKeyResource, OPENSSL_ALGO_SHA256);
        $this->assertEquals(1, $result);
    }
}
