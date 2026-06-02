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

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ModuleSeeder::class);

        // Garante que o par de chaves RSA exista para os testes
        app(KeyGeneratorService::class)->generate(true);
    }

    public function test_it_can_render_dashboard_page(): void
    {
        $response = $this->get('/portal/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('portal.dashboard');
        $response->assertSee('Painel de Controle Comercial');
    }

    public function test_it_can_render_licenses_list_page(): void
    {
        $response = $this->get('/portal/licenses');

        $response->assertStatus(200);
        $response->assertViewIs('portal.licenses');
        $response->assertSee('Licenças & Contratos');
    }

    public function test_it_can_create_new_license_via_portal(): void
    {
        $response = $this->post('/portal/licenses', [
            'client_name' => 'Alessandro dev',
            'client_email' => 'alessandro@example.com',
            'client_document' => '12.345.678/0001-99',
            'plan_name' => 'Premium Plan',
            'type' => 'subscription',
            'modules' => ['pdv', 'delivery'],
            'expires_at' => Carbon::now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/portal/licenses');
        $this->assertDatabaseHas('licenses', [
            'client_name' => 'Alessandro dev',
            'plan_name' => 'Premium Plan',
        ]);
    }

    public function test_it_can_renew_license_via_portal(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Alessandro Test',
            'client_email' => 'alessandro@example.com',
            'client_document' => '12345678901',
            'plan_name' => 'Enterprise Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $license->modules()->sync(Module::whereIn('code', ['pdv'])->pluck('id'));

        $newExpiresAt = Carbon::now()->addYears(2);

        $response = $this->post("/portal/licenses/{$license->id}/renew", [
            'expires_at' => $newExpiresAt->format('Y-m-d'),
        ]);

        $response->assertRedirect('/portal/licenses');
        $this->assertEquals($newExpiresAt->format('Y-m-d'), License::find($license->id)->expires_at->format('Y-m-d'));
    }

    public function test_it_can_suspend_license_via_portal(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Alessandro Test',
            'client_email' => 'alessandro@example.com',
            'client_document' => '12345678901',
            'plan_name' => 'Enterprise Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $license->modules()->sync(Module::whereIn('code', ['pdv'])->pluck('id'));

        $response = $this->post("/portal/licenses/{$license->id}/suspend");

        $response->assertRedirect('/portal/licenses');
        $this->assertEquals('suspended', License::find($license->id)->status);
    }

    public function test_it_can_cancel_license_via_portal(): void
    {
        /** @var License $license */
        $license = License::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'client_name' => 'Alessandro Test',
            'client_email' => 'alessandro@example.com',
            'client_document' => '12345678901',
            'plan_name' => 'Enterprise Plan',
            'type' => 'subscription',
            'status' => 'active',
            'issued_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addYear(),
        ]);

        $license->modules()->sync(Module::whereIn('code', ['pdv'])->pluck('id'));

        $response = $this->post("/portal/licenses/{$license->id}/cancel");

        $response->assertRedirect('/portal/licenses');
        $this->assertEquals('cancelled', License::find($license->id)->status);
    }

    public function test_it_can_render_modules_catalog_page(): void
    {
        $response = $this->get('/portal/modules');

        $response->assertStatus(200);
        $response->assertViewIs('portal.modules');
        $response->assertSee('Catálogo de Módulos');
    }

    public function test_it_can_create_new_module_via_portal(): void
    {
        $response = $this->post('/portal/modules', [
            'code' => 'new_module_code',
            'name' => 'New Module Name',
            'description' => 'Summary detail of commercial module.',
            'version_min' => '2.0.0',
            'price_suggested_cents' => 12900,
        ]);

        $response->assertRedirect('/portal/modules');
        $this->assertDatabaseHas('modules', [
            'code' => 'new_module_code',
            'name' => 'New Module Name',
        ]);
    }

    public function test_it_can_toggle_module_status_via_portal(): void
    {
        /** @var Module $module */
        $module = Module::where('code', 'pdv')->firstOrFail();

        $response = $this->post("/portal/modules/{$module->id}/toggle");

        $response->assertRedirect('/portal/modules');
        $this->assertEquals('inactive', Module::find($module->id)->status);
    }

    public function test_it_can_render_installations_list_page(): void
    {
        $response = $this->get('/portal/installations');

        $response->assertStatus(200);
        $response->assertViewIs('portal.installations');
        $response->assertSee('Instalações Físicas');
    }

    public function test_it_can_toggle_installation_status_via_portal(): void
    {
        /** @var LicenseInstallation $installation */
        $installation = LicenseInstallation::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'hostname' => 'client-machine-01',
            'ip_address' => '10.0.0.1',
            'fingerprint' => 'hash_test_value',
            'status' => 'active',
        ]);

        $response = $this->post("/portal/installations/{$installation->id}/toggle");

        $response->assertRedirect('/portal/installations');
        $this->assertEquals('blocked', LicenseInstallation::find($installation->id)->status);
    }

    public function test_it_can_render_audit_logs_page(): void
    {
        $response = $this->get('/portal/audit');

        $response->assertStatus(200);
        $response->assertViewIs('portal.audit');
        $response->assertSee('Auditoria Comercial');
    }
}
