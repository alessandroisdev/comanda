<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\OrderSession\CancelOrderSessionAction;
use App\Actions\OrderSession\CloseOrderSessionAction;
use App\Actions\OrderSession\MergeOrderSessionsAction;
use App\Actions\OrderSession\OpenOrderSessionAction;
use App\Actions\OrderSession\TransferTableAction;
use App\Enums\OrderSessionStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderSessionTest extends TestCase
{
    use RefreshDatabase;

    private OpenOrderSessionAction $openAction;

    private CloseOrderSessionAction $closeAction;

    private CancelOrderSessionAction $cancelAction;

    private TransferTableAction $transferAction;

    private MergeOrderSessionsAction $mergeAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openAction = app(OpenOrderSessionAction::class);
        $this->closeAction = app(CloseOrderSessionAction::class);
        $this->cancelAction = app(CancelOrderSessionAction::class);
        $this->transferAction = app(TransferTableAction::class);
        $this->mergeAction = app(MergeOrderSessionsAction::class);
    }

    #[Test]
    public function it_can_open_an_order_session_and_occupy_table()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $table = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'status' => TableStatusEnum::AVAILABLE,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $session = $this->openAction->execute([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'table_id' => $table->id,
            'opened_by_employee_id' => $employee->id,
            'people_count' => 3,
            'notes' => 'Mesa de canto',
        ]);

        $this->assertInstanceOf(OrderSession::class, $session);
        $this->assertEquals(OrderSessionStatusEnum::OPEN, $session->status);
        $this->assertEquals(TableStatusEnum::OCCUPIED, $table->fresh()->status);

        $this->assertDatabaseHas('orders_sessions', [
            'id' => $session->id,
            'table_id' => $table->id,
            'people_count' => 3,
        ]);
    }

    #[Test]
    public function it_can_close_an_order_session_and_set_table_to_cleaning()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $table = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'status' => TableStatusEnum::OCCUPIED,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $session = OrderSession::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'table_id' => $table->id,
            'status' => OrderSessionStatusEnum::OPEN,
        ]);

        $this->closeAction->execute($session, $employee->id);

        $this->assertEquals(OrderSessionStatusEnum::CLOSED, $session->fresh()->status);
        $this->assertEquals(TableStatusEnum::CLEANING, $table->fresh()->status);
        $this->assertNotNull($session->fresh()->closed_at);
    }

    #[Test]
    public function it_can_transfer_a_session_to_another_table()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $table1 = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'status' => TableStatusEnum::OCCUPIED,
        ]);
        $table2 = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'status' => TableStatusEnum::AVAILABLE,
        ]);

        $session = OrderSession::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'table_id' => $table1->id,
            'status' => OrderSessionStatusEnum::OPEN,
        ]);

        $this->transferAction->execute($session, $table2);

        $this->assertEquals($table2->id, $session->fresh()->table_id);
        $this->assertEquals(TableStatusEnum::AVAILABLE, $table1->fresh()->status);
        $this->assertEquals(TableStatusEnum::OCCUPIED, $table2->fresh()->status);
    }

    #[Test]
    public function it_can_merge_sessions()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $table1 = Table::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id, 'status' => TableStatusEnum::OCCUPIED]);
        $table2 = Table::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id, 'status' => TableStatusEnum::OCCUPIED]);

        $session1 = OrderSession::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'table_id' => $table1->id,
            'status' => OrderSessionStatusEnum::OPEN,
            'people_count' => 2,
        ]);

        $session2 = OrderSession::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'table_id' => $table2->id,
            'status' => OrderSessionStatusEnum::OPEN,
            'people_count' => 3,
        ]);

        // Cria um pedido na comanda 1
        $order = Order::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session1->id,
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $this->mergeAction->execute($session1, $session2, $employee->id);

        // A comanda 1 deve ser cancelada e liberada a mesa correspondente
        $this->assertEquals(OrderSessionStatusEnum::CANCELLED, $session1->fresh()->status);
        $this->assertEquals(TableStatusEnum::AVAILABLE, $table1->fresh()->status);

        // O pedido da comanda 1 deve ter migrado para a comanda 2
        $this->assertEquals($session2->id, $order->fresh()->session_id);

        // A contagem de pessoas na comanda 2 deve ser a soma (3 + 2 = 5)
        $this->assertEquals(5, $session2->fresh()->people_count);
    }
}
