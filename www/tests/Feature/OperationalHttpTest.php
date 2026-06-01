<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CashierShiftStatusEnum;
use App\Enums\KitchenTicketStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderSessionStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\CashierShift;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSession;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Company $company;
    private CompanyUnit $unit;
    private Employee $waiter;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuração de Tenant Principal
        $this->company = Company::factory()->create();
        $this->unit = CompanyUnit::factory()->create(['company_id' => $this->company->id]);

        // Admin Geral (bypass de policies via User)
        $this->admin = User::factory()->create();

        // Garçom do Tenant
        $this->waiter = Employee::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'role' => 'waiter',
        ]);

        // Adicionar permissões ao garçom no RBAC
        $role = Role::create(['name' => 'waiter']);
        $permissions = [
            'tables.view', 'tables.create', 'tables.update', 'tables.delete',
            'sessions.open', 'sessions.close', 'sessions.view', 'sessions.cancel', 'sessions.transfer', 'sessions.merge',
            'orders.create', 'orders.update', 'orders.view', 'orders.cancel',
            'kitchen.view', 'kitchen.update',
            'cashier.view', 'cashier.create', 'cashier.update',
        ];

        foreach ($permissions as $slug) {
            $p = Permission::firstOrCreate(['slug' => $slug]);
            $role->permissions()->attach($p);
        }
        $this->waiter->roles()->attach($role);
    }

    // ==========================================
    // MESAS (TableController)
    // ==========================================

    public function test_admin_can_view_tables_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tables.index'));
        $response->assertOk();
        $response->assertViewIs('admin.tables.index');
    }

    public function test_waiter_can_view_tables_index_with_permission()
    {
        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.tables.index'));
        $response->assertOk();
    }

    public function test_waiter_can_view_tables_create_form()
    {
        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.tables.create'));
        $response->assertOk();
    }

    public function test_waiter_can_store_table_via_http()
    {
        $data = [
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'code' => 'T-HTTP-01',
            'name' => 'Mesa HTTP 01',
            'capacity' => 6,
            'sector' => 'Térreo',
            'status' => 'available',
            'sort_order' => 10,
        ];

        $response = $this->actingAs($this->waiter, 'employee')->post(route('admin.tables.store'), $data);
        $response->assertRedirect(route('admin.tables.index'));

        $this->assertDatabaseHas('tables', [
            'code' => 'T-HTTP-01',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_waiter_can_store_table_via_json()
    {
        $data = [
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'code' => 'T-HTTP-JSON',
            'name' => 'Mesa HTTP JSON',
            'capacity' => 2,
            'sector' => 'Varanda',
            'status' => 'available',
        ];

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.tables.store'), $data);

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'table_uuid']);
    }

    public function test_waiter_can_show_table()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.tables.show', $table->uuid));
        $response->assertOk();
        $response->assertViewIs('admin.tables.show');
    }

    public function test_waiter_can_edit_table()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.tables.edit', $table->uuid));
        $response->assertOk();
        $response->assertViewIs('admin.tables.edit');
    }

    public function test_waiter_can_update_table_via_http()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'name' => 'Original Mesa Name',
        ]);

        $data = [
            'code' => $table->code,
            'name' => 'Updated Mesa Name',
            'capacity' => 10,
            'sector' => 'Cobertura',
            'sort_order' => 5,
        ];

        $response = $this->actingAs($this->waiter, 'employee')
            ->put(route('admin.tables.update', $table->uuid), $data);

        $response->assertRedirect(route('admin.tables.index'));
        $this->assertEquals('Updated Mesa Name', $table->fresh()->name);
    }

    public function test_waiter_can_change_table_status_via_http()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => TableStatusEnum::AVAILABLE,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.tables.change-status', $table->uuid), ['status' => 'cleaning']);

        $response->assertOk();
        $this->assertEquals(TableStatusEnum::CLEANING, $table->fresh()->status);
    }

    public function test_waiter_can_delete_table_via_http_ajax()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->deleteJson("/api/v1/tables/{$table->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('tables', ['id' => $table->id]);
    }

    // ==========================================
    // SESSÕES / COMANDAS (OrderSessionController)
    // ==========================================

    public function test_waiter_can_view_sessions_index()
    {
        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.sessions.index'));
        $response->assertOk();
    }

    public function test_waiter_can_open_session_via_http()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => TableStatusEnum::AVAILABLE,
        ]);

        $data = [
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'table_uuid' => $table->uuid,
            'people_count' => 4,
            'notes' => 'Comanda de teste',
        ];

        $response = $this->actingAs($this->waiter, 'employee')->post(route('admin.sessions.store'), $data);
        $response->assertRedirect();

        $this->assertDatabaseHas('orders_sessions', [
            'company_id' => $this->company->id,
            'table_id' => $table->id,
            'status' => 'open',
        ]);
    }

    public function test_waiter_can_close_session_via_http()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => TableStatusEnum::OCCUPIED,
        ]);

        $session = OrderSession::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'table_id' => $table->id,
            'status' => OrderSessionStatusEnum::OPEN,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.sessions.close', $session->uuid));

        $response->assertOk();
        $this->assertEquals(OrderSessionStatusEnum::CLOSED, $session->fresh()->status);
        $this->assertEquals(TableStatusEnum::CLEANING, $table->fresh()->status);
    }

    public function test_waiter_can_cancel_session_via_http()
    {
        $table = Table::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => TableStatusEnum::OCCUPIED,
        ]);

        $session = OrderSession::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'table_id' => $table->id,
            'status' => OrderSessionStatusEnum::OPEN,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.sessions.cancel', $session->uuid));

        $response->assertOk();
        $this->assertEquals(OrderSessionStatusEnum::CANCELLED, $session->fresh()->status);
        $this->assertEquals(TableStatusEnum::AVAILABLE, $table->fresh()->status);
    }

    public function test_waiter_can_transfer_session_via_http()
    {
        $table1 = Table::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'status' => TableStatusEnum::OCCUPIED]);
        $table2 = Table::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'status' => TableStatusEnum::AVAILABLE]);

        $session = OrderSession::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'table_id' => $table1->id,
            'status' => OrderSessionStatusEnum::OPEN,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.sessions.transfer', $session->uuid), ['target_table_uuid' => $table2->uuid]);

        $response->assertOk();
        $this->assertEquals($table2->id, $session->fresh()->table_id);
        $this->assertEquals(TableStatusEnum::AVAILABLE, $table1->fresh()->status);
        $this->assertEquals(TableStatusEnum::OCCUPIED, $table2->fresh()->status);
    }

    public function test_waiter_can_merge_sessions_via_http()
    {
        $table1 = Table::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'status' => TableStatusEnum::OCCUPIED]);
        $table2 = Table::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'status' => TableStatusEnum::OCCUPIED]);

        $session1 = OrderSession::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'table_id' => $table1->id,
            'status' => OrderSessionStatusEnum::OPEN,
            'people_count' => 2,
        ]);

        $session2 = OrderSession::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'table_id' => $table2->id,
            'status' => OrderSessionStatusEnum::OPEN,
            'people_count' => 3,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.sessions.merge', $session1->uuid), ['target_session_uuid' => $session2->uuid]);

        $response->assertOk();
        $this->assertEquals(OrderSessionStatusEnum::CANCELLED, $session1->fresh()->status);
        $this->assertEquals(5, $session2->fresh()->people_count);
    }

    // ==========================================
    // PEDIDOS (OrderController)
    // ==========================================

    public function test_waiter_can_store_order_via_http()
    {
        $session = OrderSession::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => OrderSessionStatusEnum::OPEN,
        ]);

        $data = [
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'session_uuid' => $session->uuid,
            'notes' => 'Pedido pelo HTTP',
        ];

        $response = $this->actingAs($this->waiter, 'employee')->post(route('admin.orders.store'), $data);
        $response->assertRedirect(route('admin.sessions.show', $session->uuid));

        $this->assertDatabaseHas('orders', [
            'company_id' => $this->company->id,
            'session_id' => $session->id,
            'status' => 'draft',
        ]);
    }

    public function test_waiter_can_view_order_details()
    {
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.orders.show', $order->uuid));
        $response->assertOk();
        $response->assertViewIs('admin.orders.show');
    }

    public function test_waiter_can_add_item_to_order_via_http()
    {
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'price_cents' => 1990,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.orders.items.add', $order->uuid), [
                'product_uuid' => $product->uuid,
                'quantity' => 3,
                'notes' => 'Sem cebola',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price_cents' => 1990,
            'total_price_cents' => 5970,
        ]);

        $this->assertEquals(5970, $order->fresh()->total_cents);
    }

    public function test_waiter_can_update_order_item_quantity_via_http()
    {
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $product = Product::factory()->create(['company_id' => $this->company->id, 'price_cents' => 1000]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_cents' => 1000,
            'total_price_cents' => 1000,
        ]);

        $order->update(['subtotal_cents' => 1000, 'total_cents' => 1000]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->patchJson(route('admin.orders.items.update-quantity', [$order->uuid, $item->uuid]), [
                'quantity' => 5,
            ]);

        $response->assertOk();
        $this->assertEquals(5, $item->fresh()->quantity);
        $this->assertEquals(5000, $item->fresh()->total_price_cents);
        $this->assertEquals(5000, $order->fresh()->total_cents);
    }

    public function test_waiter_can_remove_item_from_order_via_http()
    {
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => OrderStatusEnum::DRAFT,
            'total_cents' => 1500,
        ]);

        $product = Product::factory()->create(['company_id' => $this->company->id, 'price_cents' => 1500]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_cents' => 1500,
            'total_price_cents' => 1500,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->deleteJson(route('admin.orders.items.remove', [$order->uuid, $item->uuid]));

        $response->assertOk();
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
        $this->assertEquals(0, $order->fresh()->total_cents);
    }

    public function test_waiter_can_send_order_to_kitchen_via_http()
    {
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $product = Product::factory()->create(['company_id' => $this->company->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_cents' => 1000,
            'total_price_cents' => 1000,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.orders.send-to-kitchen', $order->uuid));

        $response->assertOk();
        $this->assertEquals(OrderStatusEnum::SENT_TO_KITCHEN, $order->fresh()->status);
        $this->assertDatabaseHas('kitchen_tickets', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_waiter_can_cancel_order_via_http()
    {
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.orders.cancel', $order->uuid));

        $response->assertOk();
        $this->assertEquals(OrderStatusEnum::CANCELLED, $order->fresh()->status);
    }

    // ==========================================
    // COZINHA (KitchenController)
    // ==========================================

    public function test_waiter_can_view_kitchen_tickets_index()
    {
        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.kitchen.index'));
        $response->assertOk();
        $response->assertViewIs('admin.kitchen.index');
    }

    public function test_waiter_can_start_preparing_kitchen_ticket_via_http()
    {
        $order = Order::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::PENDING,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.kitchen.start', $ticket->uuid));

        $response->assertOk();
        $this->assertEquals(KitchenTicketStatusEnum::PREPARING, $ticket->fresh()->status);
    }

    public function test_waiter_can_mark_kitchen_ticket_ready_via_http()
    {
        $order = Order::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::PREPARING,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.kitchen.ready', $ticket->uuid));

        $response->assertOk();
        $this->assertEquals(KitchenTicketStatusEnum::READY, $ticket->fresh()->status);
    }

    public function test_waiter_can_complete_kitchen_ticket_via_http()
    {
        $order = Order::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::READY,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.kitchen.complete', $ticket->uuid));

        $response->assertOk();
        $this->assertEquals(KitchenTicketStatusEnum::COMPLETED, $ticket->fresh()->status);
    }

    public function test_waiter_can_cancel_kitchen_ticket_via_http()
    {
        $order = Order::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::PENDING,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->postJson(route('admin.kitchen.cancel', $ticket->uuid));

        $response->assertOk();
        $this->assertEquals(KitchenTicketStatusEnum::CANCELLED, $ticket->fresh()->status);
    }

    // ==========================================
    // CAIXA OPERACIONAL (CashierController)
    // ==========================================

    public function test_waiter_can_view_cashier_index()
    {
        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.cashier.index'));
        $response->assertOk();
        $response->assertViewIs('admin.cashier.index');
    }

    public function test_waiter_can_open_cashier_shift_via_http()
    {
        $data = [
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'opening_amount' => 250.00,
        ];

        $response = $this->actingAs($this->waiter, 'employee')->post(route('admin.cashier.store'), $data);
        $response->assertRedirect(route('admin.cashier.index'));

        $this->assertDatabaseHas('cashier_shifts', [
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => 'open',
            'opening_amount_cents' => 25000,
        ]);
    }

    public function test_waiter_can_show_cashier_shift()
    {
        $shift = CashierShift::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')->get(route('admin.cashier.show', $shift->uuid));
        $response->assertOk();
        $response->assertViewIs('admin.cashier.show');
    }

    public function test_waiter_can_close_cashier_shift_via_http()
    {
        $shift = CashierShift::factory()->create([
            'company_id' => $this->company->id,
            'unit_id' => $this->unit->id,
            'status' => CashierShiftStatusEnum::OPEN,
            'opening_amount_cents' => 10000,
        ]);

        $response = $this->actingAs($this->waiter, 'employee')
            ->post(route('admin.cashier.close', $shift->uuid), [
                'closing_amount' => 120.50,
            ]);

        $response->assertRedirect(route('admin.cashier.index'));
        $this->assertEquals(CashierShiftStatusEnum::CLOSED, $shift->fresh()->status);
        $this->assertEquals(12050, $shift->fresh()->closing_amount_cents);
    }

    // ==========================================
    // ISOLAMENTO DE TENANT SEGURO (Multi-Tenant)
    // ==========================================

    public function test_tenant_isolation_on_all_operations()
    {
        // Outro Tenant cadastrado
        $otherCompany = Company::factory()->create();
        $otherUnit = CompanyUnit::factory()->create(['company_id' => $otherCompany->id]);
        $otherTable = Table::factory()->create([
            'company_id' => $otherCompany->id,
            'unit_id' => $otherUnit->id,
        ]);

        // Garçom do tenant principal NÃO pode ver nem modificar a mesa do outro tenant
        $response = $this->actingAs($this->waiter, 'employee')
            ->get(route('admin.tables.show', $otherTable->uuid));
        $response->assertStatus(403);

        $responseUpdate = $this->actingAs($this->waiter, 'employee')
            ->put(route('admin.tables.update', $otherTable->uuid), [
                'code' => 'T-ISOLATION',
                'name' => 'Mesa Hack',
                'capacity' => 4,
                'sector' => 'Invasão',
            ]);
        $responseUpdate->assertStatus(403);
    }
}
