<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseInstallation;
use App\Models\Module;
use App\Services\Licensing\KeyGeneratorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Popula os módulos necessários
        $this->seed(\Database\Seeders\ModuleSeeder::class);

        // Garante que o par de chaves RSA exista para os testes
        app(KeyGeneratorService::class)->generate(true);
    }

    public function test_it_can_generate_license_via_api(): void
    {
        $response = $this->postJson('/api/licenses/generate', [
            'client_name' => 'Alessandro dev',
            'client_email' => 'alessandro@example.com',
            'client_document' => '12345678901',
            'plan_name' => 'Enterprise Plan',
            'type' => 'subscription',
            'modules' => ['pdv', 'delivery'],
            'expires_at' => Carbon::now()->addYear()->toIso8601String(),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'uuid', 'client_name', 'client_email', 'status', 'expires_at'
        ]);

        $this->assertDatabaseHas('licenses', [
            'client_name' => 'Alessandro dev',
            'client_email' => 'alessandro@example.com',
        ]);
    }

    public function test_it_can_activate_license_via_api(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@doe.com',
            'client_document' => '98765432100',
            'plan_name' => 'Basic Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $license->modules()->sync(Module::whereIn('code', ['pdv', 'tables'])->pluck('id'));

        $installationUuid = (string) \Illuminate\Support\Str::uuid();

        $response = $this->postJson('/api/licenses/activate', [
            'license_uuid' => $license->uuid,
            'installation_uuid' => $installationUuid,
            'hostname' => 'server-client',
            'domain' => 'client.com',
            'ip_address' => '192.168.1.50',
            'fingerprint' => 'hash_test_fingerprint',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'license_uuid', 'activation_key', 'status', 'expires_at'
        ]);

        $this->assertDatabaseHas('license_installations', [
            'uuid' => $installationUuid,
            'hostname' => 'server-client',
        ]);

        $this->assertDatabaseHas('license_activations', [
            'license_id' => $license->id,
            'installation_uuid' => $installationUuid,
            'status' => 'active',
        ]);
    }

    public function test_it_can_renew_license_via_api(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@doe.com',
            'client_document' => '98765432100',
            'plan_name' => 'Basic Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $license->modules()->sync(Module::whereIn('code', ['pdv'])->pluck('id'));

        $newExpiresAt = Carbon::now()->addYears(2);

        $response = $this->postJson('/api/licenses/renew', [
            'license_uuid' => $license->uuid,
            'expires_at' => $newExpiresAt->toIso8601String(),
            'modules' => ['pdv', 'delivery'],
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'expires_at' => $newExpiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_it_can_suspend_license_via_api(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@doe.com',
            'client_document' => '98765432100',
            'plan_name' => 'Basic Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $response = $this->postJson('/api/licenses/suspend', [
            'license_uuid' => $license->uuid,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('suspended', License::find($license->id)->status);
    }

    public function test_it_can_cancel_license_via_api(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@doe.com',
            'client_document' => '98765432100',
            'plan_name' => 'Basic Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $response = $this->postJson('/api/licenses/cancel', [
            'license_uuid' => $license->uuid,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('cancelled', License::find($license->id)->status);
    }

    public function test_it_can_fetch_modules_via_api(): void
    {
        $response = $this->getJson('/api/modules');

        $response->assertStatus(200);
        $response->assertJsonCount(16);
    }
}
