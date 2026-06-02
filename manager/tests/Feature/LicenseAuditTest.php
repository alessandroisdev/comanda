<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Services\Licensing\LicenseIssuerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_issuance_creates_audit_log()
    {
        $license = License::create([
            'client_name' => 'Cliente Teste Audit',
            'client_email' => 'audit@example.com',
            'client_document' => '123.456.789-00',
            'plan_name' => 'Gold',
            'type' => 'monthly',
            'status' => 'active',
        ]);

        $issuer = app(LicenseIssuerService::class);
        $issuer->issue($license, ['pdv', 'waiter'], 'uuid-da-instalacao-teste');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'installation_uuid' => 'uuid-da-instalacao-teste',
            'action' => 'issue',
        ]);

        $log = LicenseAuditLog::where('license_id', $license->id)->first();
        $this->assertEquals('monthly', $log->details['type']);
        $this->assertEquals(['pdv', 'waiter'], $log->details['modules']);
    }

    public function test_license_audit_log_stores_ip_and_user_agent()
    {
        $license = License::create([
            'client_name' => 'IP User Agent Test',
            'client_email' => 'ip@example.com',
            'client_document' => '111.222.333-44',
            'plan_name' => 'Enterprise',
            'type' => 'yearly',
            'status' => 'active',
        ]);

        $issuer = app(LicenseIssuerService::class);
        $issuer->issue($license, ['pdv'], 'uuid-test-ip', 'renew');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'action' => 'renew',
        ]);

        $log = LicenseAuditLog::where('license_id', $license->id)->first();
        $this->assertNotNull($log->ip_address);
    }

    public function test_license_activation_creates_audit_log()
    {
        $license = License::create([
            'client_name' => 'Activation Audit',
            'client_email' => 'activation@example.com',
            'client_document' => '123.456.789-00',
            'plan_name' => 'Gold',
            'type' => 'monthly',
            'status' => 'active',
        ]);

        $issuer = app(LicenseIssuerService::class);
        $issuer->issue($license, ['pdv'], 'uuid-activation', 'activate');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'installation_uuid' => 'uuid-activation',
            'action' => 'activate',
        ]);
    }

    public function test_license_suspension_creates_audit_log()
    {
        $license = License::create([
            'client_name' => 'Suspension Audit',
            'client_email' => 'suspension@example.com',
            'client_document' => '123.456.789-00',
            'plan_name' => 'Gold',
            'type' => 'monthly',
            'status' => 'active',
        ]);

        $issuer = app(LicenseIssuerService::class);
        $issuer->issue($license, ['pdv'], 'uuid-suspension', 'suspend');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'installation_uuid' => 'uuid-suspension',
            'action' => 'suspend',
        ]);
    }

    public function test_license_cancellation_creates_audit_log()
    {
        $license = License::create([
            'client_name' => 'Cancellation Audit',
            'client_email' => 'cancellation@example.com',
            'client_document' => '123.456.789-00',
            'plan_name' => 'Gold',
            'type' => 'monthly',
            'status' => 'active',
        ]);

        $issuer = app(LicenseIssuerService::class);
        $issuer->issue($license, ['pdv'], 'uuid-cancellation', 'cancel');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'installation_uuid' => 'uuid-cancellation',
            'action' => 'cancel',
        ]);
    }

    public function test_license_upgrade_creates_audit_log()
    {
        $license = License::create([
            'client_name' => 'Upgrade Audit',
            'client_email' => 'upgrade@example.com',
            'client_document' => '123.456.789-00',
            'plan_name' => 'Gold',
            'type' => 'monthly',
            'status' => 'active',
        ]);

        $issuer = app(LicenseIssuerService::class);
        $issuer->issue($license, ['pdv', 'delivery'], 'uuid-upgrade', 'upgrade');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'installation_uuid' => 'uuid-upgrade',
            'action' => 'upgrade',
        ]);
    }
}
