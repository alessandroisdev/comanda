<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\SendOrderToKitchenAction;
use App\Actions\OrderItem\AddOrderItemAction;
use App\Actions\OrderItem\RemoveOrderItemAction;
use App\Actions\OrderItem\UpdateOrderItemQuantityAction;
use App\Enums\OrderStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSession;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private CreateOrderAction $createOrderAction;

    private CancelOrderAction $cancelOrderAction;

    private SendOrderToKitchenAction $sendToKitchenAction;

    private AddOrderItemAction $addItemAction;

    private RemoveOrderItemAction $removeItemAction;

    private UpdateOrderItemQuantityAction $updateItemQtyAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOrderAction = app(CreateOrderAction::class);
        $this->cancelOrderAction = app(CancelOrderAction::class);
        $this->sendToKitchenAction = app(SendOrderToKitchenAction::class);
        $this->addItemAction = app(AddOrderItemAction::class);
        $this->removeItemAction = app(RemoveOrderItemAction::class);
        $this->updateItemQtyAction = app(UpdateOrderItemQuantityAction::class);
    }

    #[Test]
    public function it_can_create_an_order_as_draft()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $session = OrderSession::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $order = $this->createOrderAction->execute([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'discount_cents' => 0,
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(OrderStatusEnum::DRAFT, $order->status);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function it_can_add_items_to_an_order_and_recalculate_totals()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $session = OrderSession::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $order = Order::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'subtotal_cents' => 0,
            'total_cents' => 0,
        ]);

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'price_cents' => 1500, // R$ 15,00
        ]);

        // Adiciona 2 unidades
        $item = $this->addItemAction->execute($order, $product, 2, 'Sem cebola');

        $this->assertInstanceOf(OrderItem::class, $item);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(1500, $item->unit_price_cents);
        $this->assertEquals(3000, $item->total_price_cents);

        // O pedido total deve ser R$ 30,00 (3000 centavos)
        $this->assertEquals(3000, $order->fresh()->total_cents);
    }

    #[Test]
    public function it_can_update_item_quantity_and_recalculate_totals()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $session = OrderSession::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id, 'session_id' => $session->id]);
        $product = Product::factory()->create(['company_id' => $company->id, 'price_cents' => 1000]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_cents' => 1000,
            'total_price_cents' => 1000,
        ]);

        $this->updateItemQtyAction->execute($item, 3);

        $this->assertEquals(3, $item->fresh()->quantity);
        $this->assertEquals(3000, $item->fresh()->total_price_cents);
        $this->assertEquals(3000, $order->fresh()->total_cents);
    }

    #[Test]
    public function it_can_remove_item_and_recalculate_totals()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $session = OrderSession::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id, 'session_id' => $session->id]);
        $product = Product::factory()->create(['company_id' => $company->id, 'price_cents' => 1200]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price_cents' => 1200,
            'total_price_cents' => 2400,
        ]);

        $order->update(['subtotal_cents' => 2400, 'total_cents' => 2400]);

        $this->removeItemAction->execute($item);

        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
        $this->assertEquals(0, $order->fresh()->total_cents);
    }

    #[Test]
    public function it_can_send_an_order_to_the_kitchen()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $session = OrderSession::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $order = Order::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $product = Product::factory()->create(['company_id' => $company->id]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_cents' => $product->price_cents,
            'total_price_cents' => $product->price_cents,
        ]);

        $this->sendToKitchenAction->execute($order);

        $this->assertEquals(OrderStatusEnum::SENT_TO_KITCHEN, $order->fresh()->status);
        $this->assertDatabaseHas('kitchen_tickets', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('print_jobs', [
            'company_id' => $company->id,
            'type' => 'kitchen_ticket',
        ]);
    }
}
