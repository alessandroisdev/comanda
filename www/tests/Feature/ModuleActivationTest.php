<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LicenseStatusEnum;
use App\Models\User;
use App\Services\Licensing\LicenseManager;
use App\Services\Licensing\LicenseValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModuleActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ignora os middlewares (evitando CSRF Token Mismatch e session check)
        $this->withoutMiddleware();

        // Garantir que a autorização viewAnyModule retorne true para os testes
        Gate::define('viewAnyModule', fn () => true);
    }

    #[Test]
    public function it_can_display_the_modules_page_with_licensing_details(): void
    {
        $this->withoutExceptionHandling();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/modules');

        $response->assertStatus(200);
        $response->assertSee('Módulos Ativos');
        $response->assertSee('Status de Licenciamento');
        $response->assertSee('Identificador da Instalação Física (UUID)');
    }

    #[Test]
    public function online_activation_requires_valid_parameters(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/admin/modules/activate-online', [
            'manager_url' => 'not-a-url',
            'license_uuid' => 'not-a-uuid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['manager_url', 'license_uuid']);
    }

    #[Test]
    public function online_activation_can_successfully_activate_license(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Fake do Manager Comercial
        Http::fake([
            'https://manager.comanda.com.br/api/licenses/activate' => Http::response([
                'success' => true,
                'license_uuid' => '3ee1a5eb-fa6c-482a-a92e-3367b66f22cd',
                'activation_key' => base64_encode(json_encode([
                    'id' => 123,
                    'installation_uuid' => 'f2a24bc1-5369-42b7-bd20-1e5b22b109cc',
                    'expires_at' => '2027-06-01T12:00:00Z',
                    'status' => 'active',
                    'signature' => 'mock_signature',
                ])),
                'status' => 'active',
                'expires_at' => '2027-06-01T12:00:00Z',
            ], 200),
        ]);

        // Mock do validador local de licenças para aprovar a ativação fake
        $validatorMock = $this->mock(LicenseValidator::class);
        $validatorMock->shouldReceive('getLocalInstallationUuid')->andReturn('f2a24bc1-5369-42b7-bd20-1e5b22b109cc');
        $validatorMock->shouldReceive('validate')->andReturn(LicenseStatusEnum::ACTIVE);

        // Mock do LicenseManager para persistir
        $this->mock(LicenseManager::class, function ($mock) {
            $mock->shouldReceive('activate')->once()->andReturn(true);
        });

        $response = $this->actingAs($user)->postJson('/admin/modules/activate-online', [
            'manager_url' => 'https://manager.comanda.com.br',
            'license_uuid' => '3ee1a5eb-fa6c-482a-a92e-3367b66f22cd',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Licença online ativada e módulos desbloqueados com sucesso!',
        ]);
    }

    #[Test]
    public function offline_activation_requires_activation_key(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/admin/modules/activate-offline', [
            'activation_key' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['activation_key']);
    }

    #[Test]
    public function offline_activation_can_successfully_activate_raw_key(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $mockKey = base64_encode(json_encode([
            'id' => 123,
            'installation_uuid' => 'f2a24bc1-5369-42b7-bd20-1e5b22b109cc',
            'expires_at' => '2027-06-01T12:00:00Z',
            'status' => 'active',
            'signature' => 'mock_signature',
        ]));

        $this->mock(LicenseManager::class, function ($mock) use ($mockKey) {
            $mock->shouldReceive('activate')->once()->andReturn(true);
        });

        $response = $this->actingAs($user)->postJson('/admin/modules/activate-offline', [
            'activation_key' => $mockKey,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Licença offline ativada e módulos desbloqueados com sucesso!',
        ]);
    }
}
