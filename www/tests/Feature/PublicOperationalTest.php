<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Product;
use App\Models\Table;
use App\Services\SSE\SseQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicOperationalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function public_menu_can_be_rendered_without_table()
    {
        $company = Company::factory()->create();

        $response = $this->get(route('public.menu', ['company_id' => $company->id]));

        $response->assertStatus(200);
        $response->assertViewIs('public.menu.index');
        $response->assertSee('COMANDA');
    }

    #[Test]
    public function public_menu_resolves_deep_link_via_table_slug()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-PWA',
            'name' => 'Mesa PWA',
            'capacity' => 4,
            'sector' => 'Varanda',
            'status' => 'available',
        ]);

        $response = $this->get(route('public.menu.table', ['slug' => $table->slug]));

        $response->assertStatus(200);
        $response->assertSessionHas('public_table_uuid', $table->public_uuid);
        $response->assertSee('Mesa PWA');
    }

    #[Test]
    public function it_renders_table_permanent_qrcode_svg()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-QR',
            'name' => 'Mesa QR',
            'capacity' => 6,
            'sector' => 'Salão',
            'status' => 'available',
        ]);

        $response = $this->get(route('public.menu.qrcode', ['public_uuid' => $table->public_uuid]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertSee('svg');
        $response->assertSee('MESA');
    }

    #[Test]
    public function it_can_retrieve_categories_and_products_via_api()
    {
        $company = Company::factory()->create();
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Bebidas',
            'status' => 'active',
            'sort_order' => 1,
        ]);
        Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'SUCO-01',
            'name' => 'Suco de Laranja',
            'price_cents' => 800,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/v1/menu/categories?company_id={$company->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Bebidas']);

        $responseProduct = $this->getJson("/api/v1/menu/products?company_id={$company->id}");
        $responseProduct->assertStatus(200);
        $responseProduct->assertJsonFragment(['name' => 'Suco de Laranja']);
    }

    #[Test]
    public function customer_can_call_waiter_via_api_and_trigger_sse()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-CALL',
            'name' => 'Mesa Chamado',
            'capacity' => 2,
            'sector' => 'Salão',
            'status' => 'available',
        ]);

        $this->withoutMiddleware();

        // Limpar o cache de eventos SSE antes de testar
        Cache::forget('sse_events:admin.tables');

        $response = $this->postJson("/api/v1/tables/{$table->public_uuid}/call-waiter");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Valida que o evento SSE reativo da Fase 4 foi corretamente publicado em Cache
        $events = SseQueueService::pull('admin.tables');
        $this->assertNotEmpty($events);
        $this->assertEquals('waiter.called', $events[0]['event']);
        $this->assertEquals($table->uuid, $events[0]['data']['table_uuid']);
    }

    #[Test]
    public function customer_can_request_bill_via_api_and_trigger_sse()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $table = Table::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-BILL',
            'name' => 'Mesa Fechamento',
            'capacity' => 4,
            'sector' => 'Salão',
            'status' => 'available',
        ]);

        $this->withoutMiddleware();

        Cache::forget('sse_events:admin.tables');

        $response = $this->postJson("/api/v1/tables/{$table->public_uuid}/request-bill");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $events = SseQueueService::pull('admin.tables');
        $this->assertNotEmpty($events);
        $this->assertEquals('bill.requested', $events[0]['event']);
    }
}
