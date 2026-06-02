<?php

namespace Tests\Feature;

use App\Enums\KitchenTicketStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\DeliveryOrder;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\Table;
use App\Services\Monitoring\BusinessMetricsService;
use App\Services\Monitoring\DatabaseMetricsService;
use App\Services\Monitoring\HealthMetricsService;
use App\Services\Monitoring\MetricsService;
use App\Services\Monitoring\QueueMetricsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MonitoringMetricsTest extends TestCase
{
    use RefreshDatabase;

    private Company $company1;
    private Company $company2;
    private CompanyUnit $unit1;
    private CompanyUnit $unit2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company1 = Company::create(['name' => 'Restaurante 1', 'document' => '12345678000101', 'email' => 'r1@test.com']);
        $this->company2 = Company::create(['name' => 'Restaurante 2', 'document' => '12345678000102', 'email' => 'r2@test.com']);

        $this->unit1 = CompanyUnit::create(['company_id' => $this->company1->id, 'name' => 'Unidade Centro', 'email' => 'centro@test.com']);
        $this->unit2 = CompanyUnit::create(['company_id' => $this->company2->id, 'name' => 'Unidade Sul', 'email' => 'sul@test.com']);
    }

    public function test_business_metrics_empty_defaults()
    {
        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(0, $metrics['orders_last_hour']);
        $this->assertEquals(0, $metrics['average_ticket_cents']);
        $this->assertEquals(0, $metrics['sales_today_cents']);
        $this->assertEquals(0, $metrics['occupied_tables']);
        $this->assertEquals(0, $metrics['deliveries_in_progress']);
        $this->assertEquals(0, $metrics['orders_in_production']);
    }

    public function test_business_metrics_orders_last_hour()
    {
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 5000, 'status' => OrderStatusEnum::PENDING]);
        
        Carbon::setTestNow(Carbon::now()->addMinutes(30));
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 6000, 'status' => OrderStatusEnum::PREPARING]);

        Carbon::setTestNow(Carbon::now()->addMinutes(45)); // mais de 1 hora da primeira ordem

        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(1, $metrics['orders_last_hour']); // Apenas a segunda criada nos últimos 60 minutos do novo "now"
        Carbon::setTestNow(); // Reset time
    }

    public function test_business_metrics_average_ticket_calculation()
    {
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 4000, 'status' => OrderStatusEnum::PENDING]);
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 8000, 'status' => OrderStatusEnum::DELIVERED]);
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 9000, 'status' => OrderStatusEnum::CANCELLED]); // Cancelado deve ser ignorado

        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(6000, $metrics['average_ticket_cents']); // (4000 + 8000) / 2 = 6000
    }

    public function test_business_metrics_sales_today_calculation()
    {
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 3500, 'status' => OrderStatusEnum::PENDING]);
        
        // Simular ontem
        $yesterday = Carbon::now()->subDay();
        Carbon::setTestNow($yesterday);
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 9900, 'status' => OrderStatusEnum::PENDING]);
        
        Carbon::setTestNow(); // Reset time para hoje

        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(3500, $metrics['sales_today_cents']); // Apenas a de hoje
    }

    public function test_business_metrics_occupied_tables_count()
    {
        Table::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'number' => '10', 'status' => TableStatusEnum::OCCUPIED]);
        Table::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'number' => '11', 'status' => TableStatusEnum::AVAILABLE]);

        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(1, $metrics['occupied_tables']);
    }

    public function test_business_metrics_deliveries_in_progress_count()
    {
        DeliveryOrder::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'status' => 'assigned', 'customer_id' => 1, 'delivery_zone_id' => 1, 'delivery_fee_id' => 1, 'address_street' => 'Rua A', 'address_number' => '100', 'address_neighborhood' => 'Centro', 'address_city' => 'Cidade', 'address_state' => 'SP', 'address_zipcode' => '01000-000']);
        DeliveryOrder::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'status' => 'dispatched', 'customer_id' => 1, 'delivery_zone_id' => 1, 'delivery_fee_id' => 1, 'address_street' => 'Rua B', 'address_number' => '200', 'address_neighborhood' => 'Centro', 'address_city' => 'Cidade', 'address_state' => 'SP', 'address_zipcode' => '01000-000']);
        DeliveryOrder::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'status' => 'delivered', 'customer_id' => 1, 'delivery_zone_id' => 1, 'delivery_fee_id' => 1, 'address_street' => 'Rua C', 'address_number' => '300', 'address_neighborhood' => 'Centro', 'address_city' => 'Cidade', 'address_state' => 'SP', 'address_zipcode' => '01000-000']); // entregue não está em progresso

        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(2, $metrics['deliveries_in_progress']);
    }

    public function test_business_metrics_orders_in_production_count()
    {
        $order1 = Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 3000, 'status' => OrderStatusEnum::PENDING]);
        $order2 = Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 4000, 'status' => OrderStatusEnum::PENDING]);

        KitchenTicket::create(['order_id' => $order1->id, 'status' => KitchenTicketStatusEnum::PENDING]);
        KitchenTicket::create(['order_id' => $order2->id, 'status' => KitchenTicketStatusEnum::PREPARING]);

        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();

        $this->assertEquals(2, $metrics['orders_in_production']);
    }

    public function test_business_metrics_filters_by_company_id()
    {
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 5000, 'status' => OrderStatusEnum::PENDING]);
        Order::create(['company_id' => $this->company2->id, 'unit_id' => $this->unit2->id, 'total_cents' => 7000, 'status' => OrderStatusEnum::PENDING]);

        $service = app(BusinessMetricsService::class);

        // Sem filtro
        $metricsAll = $service->getBusinessMetrics();
        $this->assertEquals(12000, $metricsAll['sales_today_cents']);

        // Com filtro Company 1
        $metricsCompany1 = $service->getBusinessMetrics($this->company1->id);
        $this->assertEquals(5000, $metricsCompany1['sales_today_cents']);

        // Com filtro Company 2
        $metricsCompany2 = $service->getBusinessMetrics($this->company2->id);
        $this->assertEquals(7000, $metricsCompany2['sales_today_cents']);
    }

    public function test_business_metrics_filters_by_unit_id()
    {
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 5000, 'status' => OrderStatusEnum::PENDING]);
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit2->id, 'total_cents' => 3000, 'status' => OrderStatusEnum::PENDING]); // Unidade 2

        $service = app(BusinessMetricsService::class);

        $metricsUnit1 = $service->getBusinessMetrics(null, $this->unit1->id);
        $this->assertEquals(5000, $metricsUnit1['sales_today_cents']);

        $metricsUnit2 = $service->getBusinessMetrics(null, $this->unit2->id);
        $this->assertEquals(3000, $metricsUnit2['sales_today_cents']);
    }

    public function test_business_metrics_filters_by_company_and_unit()
    {
        Order::create(['company_id' => $this->company1->id, 'unit_id' => $this->unit1->id, 'total_cents' => 5000, 'status' => OrderStatusEnum::PENDING]);
        
        $service = app(BusinessMetricsService::class);

        $metricsFiltered = $service->getBusinessMetrics($this->company1->id, $this->unit1->id);
        $this->assertEquals(5000, $metricsFiltered['sales_today_cents']);

        $metricsMissed = $service->getBusinessMetrics($this->company2->id, $this->unit1->id);
        $this->assertEquals(0, $metricsMissed['sales_today_cents']);
    }

    public function test_database_metrics_status_returns_up()
    {
        $service = app(DatabaseMetricsService::class);
        $metrics = $service->getDatabaseMetrics();

        $this->assertEquals('up', $metrics['status']);
    }

    public function test_database_metrics_handles_pdo_failures_gracefully()
    {
        DB::shouldReceive('select')->andThrow(new \Exception('Database Connection Fail'));

        $service = app(DatabaseMetricsService::class);
        $metrics = $service->getDatabaseMetrics();

        $this->assertEquals('up', $metrics['status']);
        $this->assertEquals(1, $metrics['connections']);
        $this->assertEquals(0, $metrics['slow_queries_count']);

        DB::shouldReceive(); // reset mocks
    }

    public function test_database_metrics_returns_valid_integers()
    {
        $service = app(DatabaseMetricsService::class);
        $metrics = $service->getDatabaseMetrics();

        $this->assertIsInt($metrics['connections']);
        $this->assertIsInt($metrics['slow_queries_count']);
    }

    public function test_queue_metrics_status_returns_valid_structure()
    {
        $service = app(QueueMetricsService::class);
        $metrics = $service->getQueueMetrics();

        $this->assertArrayHasKey('pending_jobs', $metrics);
        $this->assertArrayHasKey('failed_jobs', $metrics);
    }

    public function test_queue_metrics_handles_redis_connection_exception()
    {
        Redis::shouldReceive('connection')->andThrow(new \Exception('Redis connection fail'));

        $service = app(QueueMetricsService::class);
        $metrics = $service->getQueueMetrics();

        $this->assertEquals(0, $metrics['pending_jobs']);
        $this->assertEquals(0, $metrics['failed_jobs']);

        Redis::shouldReceive(); // reset mocks
    }

    public function test_queue_metrics_counts_correctly()
    {
        Queue::shouldReceive('size')->andReturn(4);
        DB::shouldReceive('table')->andReturnSelf();
        DB::shouldReceive('count')->andReturn(2);

        $service = app(QueueMetricsService::class);
        $metrics = $service->getQueueMetrics();

        $this->assertEquals(4, $metrics['pending_jobs']);
        $this->assertEquals(2, $metrics['failed_jobs']);

        DB::shouldReceive(); // reset mocks
    }

    public function test_health_metrics_cpu_is_numeric()
    {
        $service = app(HealthMetricsService::class);
        $stats = $service->getSystemMetrics();

        $this->assertIsFloat($stats['cpu_load_percent']);
        $this->assertGreaterThanOrEqual(0, $stats['cpu_load_percent']);
    }

    public function test_health_metrics_memory_fields_are_positive()
    {
        $service = app(HealthMetricsService::class);
        $stats = $service->getSystemMetrics();

        $this->assertGreaterThan(0, $stats['memory']['total_bytes']);
        $this->assertGreaterThanOrEqual(0, $stats['memory']['used_bytes']);
        $this->assertGreaterThanOrEqual(0, $stats['memory']['free_bytes']);
        $this->assertGreaterThanOrEqual(0, $stats['memory']['used_percent']);
    }

    public function test_health_metrics_disk_fields_are_positive()
    {
        $service = app(HealthMetricsService::class);
        $stats = $service->getSystemMetrics();

        $this->assertGreaterThan(0, $stats['disk']['total_bytes']);
        $this->assertGreaterThanOrEqual(0, $stats['disk']['used_bytes']);
        $this->assertGreaterThanOrEqual(0, $stats['disk']['free_bytes']);
        $this->assertGreaterThanOrEqual(0, $stats['disk']['used_percent']);
    }

    public function test_metrics_aggregator_combines_all_outputs_correctly()
    {
        $service = app(MetricsService::class);
        $metrics = $service->getFullMetrics();

        $this->assertArrayHasKey('timestamp', $metrics);
        $this->assertArrayHasKey('system', $metrics);
        $this->assertArrayHasKey('database', $metrics);
        $this->assertArrayHasKey('queue', $metrics);
        $this->assertArrayHasKey('business', $metrics);
    }

    public function test_metrics_aggregator_passes_company_and_unit_parameters()
    {
        $service = app(MetricsService::class);
        $metrics = $service->getFullMetrics($this->company1->id, $this->unit1->id);

        $this->assertArrayHasKey('business', $metrics);
        $this->assertEquals(0, $metrics['business']['sales_today_cents']);
    }

    public function test_business_metrics_average_ticket_zero_orders()
    {
        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();
        $this->assertEquals(0, $metrics['average_ticket_cents']);
    }

    public function test_business_metrics_sales_today_zero_orders()
    {
        $service = app(BusinessMetricsService::class);
        $metrics = $service->getBusinessMetrics();
        $this->assertEquals(0, $metrics['sales_today_cents']);
    }

    public function test_queue_metrics_handles_sync_connection()
    {
        config(['queue.default' => 'sync']);
        $service = app(QueueMetricsService::class);
        $metrics = $service->getQueueMetrics();
        $this->assertEquals(0, $metrics['pending_jobs']);
    }

    public function test_health_metrics_cpu_returns_numeric_value()
    {
        $service = app(HealthMetricsService::class);
        $metrics = $service->getSystemMetrics();
        $this->assertIsNumeric($metrics['cpu_load_percent']);
    }
}
