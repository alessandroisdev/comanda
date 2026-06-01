<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\CashierShift\CloseCashierShiftAction;
use App\Actions\CashierShift\OpenCashierShiftAction;
use App\Enums\CashierShiftStatusEnum;
use App\Models\CashierShift;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CashierShiftTest extends TestCase
{
    use RefreshDatabase;

    private OpenCashierShiftAction $openAction;

    private CloseCashierShiftAction $closeAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openAction = app(OpenCashierShiftAction::class);
        $this->closeAction = app(CloseCashierShiftAction::class);
    }

    #[Test]
    public function it_can_open_a_cashier_shift()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $shift = $this->openAction->execute([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'opened_by' => $employee->id,
            'opening_amount_cents' => 15000, // R$ 150,00
        ]);

        $this->assertInstanceOf(CashierShift::class, $shift);
        $this->assertEquals(CashierShiftStatusEnum::OPEN, $shift->status);
        $this->assertEquals(15000, $shift->opening_amount_cents);
        $this->assertNull($shift->closing_amount_cents);

        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shift->id,
            'status' => 'open',
            'opening_amount_cents' => 15000,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'cashier.open_shift',
        ]);
    }

    #[Test]
    public function it_can_close_a_cashier_shift_with_perfect_reconciliation()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $shift = CashierShift::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'opened_by' => $employee->id,
            'opening_amount_cents' => 10000,
            'status' => CashierShiftStatusEnum::OPEN,
        ]);

        $this->closeAction->execute($shift, $employee->id, 10000);

        $this->assertEquals(CashierShiftStatusEnum::CLOSED, $shift->fresh()->status);
        $this->assertEquals(10000, $shift->fresh()->closing_amount_cents);
        $this->assertNotNull($shift->fresh()->closed_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'cashier.close_shift',
        ]);
    }
}
