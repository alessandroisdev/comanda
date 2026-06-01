<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\PrintJob\EnqueuePrintJobAction;
use App\Enums\PrintJobStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\PrintJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrintingTest extends TestCase
{
    use RefreshDatabase;

    private EnqueuePrintJobAction $enqueueAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enqueueAction = app(EnqueuePrintJobAction::class);
    }

    #[Test]
    public function it_can_enqueue_a_print_job_correctly()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $payload = [
            'order_number' => 'PED-1002',
            'items' => [
                ['name' => 'Coca-Cola 350ml', 'quantity' => 2],
                ['name' => 'Hambúrguer Gourmet', 'quantity' => 1],
            ],
            'total' => 'R$ 45,50',
        ];

        $job = $this->enqueueAction->execute([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'type' => 'kitchen_ticket',
            'payload' => $payload,
        ]);

        $this->assertInstanceOf(PrintJob::class, $job);
        $this->assertEquals(PrintJobStatusEnum::PENDING, $job->status);
        $this->assertEquals(0, $job->attempts);
        $this->assertEquals('kitchen_ticket', $job->type);
        $this->assertEquals($payload, $job->payload);

        $this->assertDatabaseHas('print_jobs', [
            'status' => 'pending',
            'type' => 'kitchen_ticket',
        ]);

        $auditLog = DB::table('audit_logs')->where('action', 'print.enqueue')->first();
        $this->assertNotNull($auditLog);
        $beforeData = json_decode($auditLog->payload_before, true);
        $this->assertEquals($company->id, $beforeData['company_id']);
    }

    #[Test]
    public function it_stores_and_retrieves_json_payloads_safely()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $payload = ['foo' => 'bar', 'nested' => ['a' => 1, 'b' => true]];

        $job = $this->enqueueAction->execute([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'type' => 'receipt',
            'payload' => $payload,
        ]);

        $retrieved = PrintJob::find($job->id);
        $this->assertEquals($payload, $retrieved->payload);
        $this->assertTrue($retrieved->payload['nested']['b']);
    }
}
