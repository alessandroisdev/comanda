<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\Product;
use App\Services\SSE\SseQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TotemTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_open_totem_view()
    {
        $company = Company::factory()->create();

        $response = $this->get(route('public.menu.totem', ['company_id' => $company->id]));

        $response->assertStatus(200);
        $response->assertViewIs('public.menu.totem');
    }

    #[Test]
    public function it_can_place_order_via_totem()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Bebidas',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'REFR-01',
            'name' => 'Coca Cola',
            'price_cents' => 600, // R$ 6,00
            'status' => 'active',
        ]);

        Cache::forget('sse_events:admin.orders');

        $response = $this->postJson('/api/v1/totem/order', [
            'option' => 'local',
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotNull($response->json('senha'));

        // Valida que o pedido e comanda local do totem foram salvos com o valor correto
        $this->assertDatabaseHas('orders', [
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'subtotal_cents' => 1800,
            'total_cents' => 1800,
        ]);

        // Valida o SSE disparado para a produção da cozinha
        $events = SseQueueService::pull('admin.orders');
        $this->assertNotEmpty($events);
        $this->assertEquals('order.created', $events[0]['event']);
        $this->assertEquals('Totem Autoatendimento', $events[0]['data']['table_name']);
    }

    #[Test]
    public function it_fails_totem_checkout_with_empty_items()
    {
        $this->withoutMiddleware();
        $response = $this->postJson('/api/v1/totem/order', [
            'option' => 'local',
            'items' => [],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Carrinho vazio.',
            ]);
    }

    #[Test]
    public function it_fails_totem_checkout_if_no_employee()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Bebidas',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'REFR-02',
            'name' => 'Fanta',
            'price_cents' => 600,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/totem/order', [
            'option' => 'local',
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Totem inoperante: sem funcionários registrados.',
            ]);
    }
}
