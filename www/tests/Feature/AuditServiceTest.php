<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditService();
    }

    #[Test]
    public function it_persists_audit_records_in_database()
    {
        $action = 'order.discount.apply';
        $before = ['discount' => 0];
        $after = ['discount' => 1000];
        $context = ['order_uuid' => 'f3b392a8-129b-43d9-a9a3-a5c7c2512f45'];

        $this->service->log($action, $before, $after, $context);

        $this->assertDatabaseHas('audit_logs', [
            'action' => $action,
            'actor_type' => 'guest',
        ]);

        $record = DB::table('audit_logs')->where('action', $action)->first();
        $this->assertNotNull($record);
        $this->assertEquals(json_encode($before), $record->payload_before);
        $this->assertEquals(json_encode($after), $record->payload_after);
    }
}
