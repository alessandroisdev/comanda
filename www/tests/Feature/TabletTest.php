<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Models\Employee;
use App\Enums\TableStatusEnum;
use App\Services\SSE\SseQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TabletTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_open_tablet_view_and_change_table_status()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        
        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-TAB',
            'name' => 'Mesa Tablet 1',
            'capacity' => 4,
            'sector' => 'Salão',
            'status' => 'available',
        ]);

        $response = $this->get(route('public.menu.tablet', ['public_uuid' => $table->public_uuid]));

        $response->assertStatus(200);
        $response->assertViewIs('public.menu.tablet');
        $response->assertSee('Mesa Tablet 1');

        // Confirma que a mesa mudou reativamente para occupied
        $table->refresh();
        $this->assertEquals(TableStatusEnum::OCCUPIED, $table->status);
    }

    #[Test]
    public function it_can_place_order_via_tablet()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-TAB2',
            'name' => 'Mesa Tablet 2',
            'capacity' => 4,
            'sector' => 'Salão',
            'status' => 'occupied',
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Burgers',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'BURGER-01',
            'name' => 'Premium Burger',
            'price_cents' => 3500, // R$ 35,00
            'status' => 'active',
        ]);

        Cache::forget("sse_events:admin.orders");

        $response = $this->postJson('/api/v1/tablet/order', [
            'table_uuid' => $table->public_uuid,
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 2]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Valida que o pedido e itens foram salvos no BD com o valor correto
        $this->assertDatabaseHas('orders', [
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'subtotal_cents' => 7000,
            'total_cents' => 7000,
        ]);

        // Valida o SSE reativo disparado para o painel
        $events = SseQueueService::pull('admin.orders');
        $this->assertNotEmpty($events);
        $this->assertEquals('order.created', $events[0]['event']);
    }

    #[Test]
    public function it_can_call_waiter_via_tablet_endpoint()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        
        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-TAB-CALL',
            'name' => 'Mesa 5',
            'capacity' => 4,
            'sector' => 'Salão',
            'status' => 'occupied',
        ]);

        Cache::forget("sse_events:admin.tables");

        $response = $this->postJson("/api/v1/tables/{$table->public_uuid}/call-waiter");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $events = SseQueueService::pull('admin.tables');
        $this->assertNotEmpty($events);
        $this->assertEquals('waiter.called', $events[0]['event']);
    }

    #[Test]
    public function it_can_request_bill_via_tablet_endpoint()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        
        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-TAB-BILL',
            'name' => 'Mesa 6',
            'capacity' => 4,
            'sector' => 'Salão',
            'status' => 'occupied',
        ]);

        Cache::forget("sse_events:admin.tables");

        $response = $this->postJson("/api/v1/tables/{$table->public_uuid}/request-bill");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $events = SseQueueService::pull('admin.tables');
        $this->assertNotEmpty($events);
        $this->assertEquals('bill.requested', $events[0]['event']);
    }
}
