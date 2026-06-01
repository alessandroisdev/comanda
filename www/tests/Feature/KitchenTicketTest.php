<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\KitchenTicket\CancelKitchenTicketAction;
use App\Actions\KitchenTicket\CompleteKitchenTicketAction;
use App\Actions\KitchenTicket\CreateKitchenTicketAction;
use App\Actions\KitchenTicket\MarkKitchenReadyAction;
use App\Actions\KitchenTicket\StartKitchenPreparoAction;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Enums\KitchenTicketStatusEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KitchenTicketTest extends TestCase
{
    use RefreshDatabase;

    private CreateKitchenTicketAction $createAction;
    private StartKitchenPreparoAction $startAction;
    private MarkKitchenReadyAction $readyAction;
    private CompleteKitchenTicketAction $completeAction;
    private CancelKitchenTicketAction $cancelAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateKitchenTicketAction::class);
        $this->startAction = app(StartKitchenPreparoAction::class);
        $this->readyAction = app(MarkKitchenReadyAction::class);
        $this->completeAction = app(CompleteKitchenTicketAction::class);
        $this->cancelAction = app(CancelKitchenTicketAction::class);
    }

    #[Test]
    public function it_can_create_a_kitchen_ticket_from_order()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $ticket = $this->createAction->execute($order);

        $this->assertInstanceOf(KitchenTicket::class, $ticket);
        $this->assertEquals(KitchenTicketStatusEnum::PENDING, $ticket->status);
        $this->assertDatabaseHas('kitchen_tickets', [
            'id' => $ticket->id,
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_can_start_preparing_a_ticket()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::PENDING,
        ]);

        $this->startAction->execute($ticket);

        $this->assertEquals(KitchenTicketStatusEnum::PREPARING, $ticket->fresh()->status);
        $this->assertEquals(OrderStatusEnum::PREPARING, $order->fresh()->status);
        $this->assertNotNull($ticket->fresh()->started_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'kitchen.start_preparing',
        ]);
    }

    #[Test]
    public function it_can_mark_a_ticket_as_ready()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::PREPARING,
        ]);

        $this->readyAction->execute($ticket);

        $this->assertEquals(KitchenTicketStatusEnum::READY, $ticket->fresh()->status);
        $this->assertEquals(OrderStatusEnum::READY, $order->fresh()->status);
        $this->assertNotNull($ticket->fresh()->ready_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'kitchen.mark_ready',
        ]);
    }

    #[Test]
    public function it_can_complete_a_ticket()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::READY,
        ]);

        $this->completeAction->execute($ticket);

        $this->assertEquals(KitchenTicketStatusEnum::COMPLETED, $ticket->fresh()->status);
        $this->assertEquals(OrderStatusEnum::DELIVERED, $order->fresh()->status);
        $this->assertNotNull($ticket->fresh()->completed_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'kitchen.complete',
        ]);
    }

    #[Test]
    public function it_can_cancel_a_ticket()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $order = Order::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);
        $ticket = KitchenTicket::factory()->create([
            'order_id' => $order->id,
            'status' => KitchenTicketStatusEnum::PENDING,
        ]);

        $this->cancelAction->execute($ticket);

        $this->assertEquals(KitchenTicketStatusEnum::CANCELLED, $ticket->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'kitchen.cancel',
        ]);
    }
}
