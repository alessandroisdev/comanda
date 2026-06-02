<?php

namespace Tests\Feature;

use App\Services\Monitoring\BusinessMetricsService;
use App\Services\Monitoring\DatabaseMetricsService;
use App\Services\Monitoring\HealthMetricsService;
use App\Services\Monitoring\MetricsService;
use App\Services\Monitoring\QueueMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_service_returns_structured_aggregated_payload()
    {
        $service = app(MetricsService::class);
        $metrics = $service->getFullMetrics();

        $this->assertArrayHasKey('timestamp', $metrics);
        $this->assertArrayHasKey('system', $metrics);
        $this->assertArrayHasKey('database', $metrics);
        $this->assertArrayHasKey('queue', $metrics);
        $this->assertArrayHasKey('business', $metrics);
    }

    public function test_health_metrics_service_returns_system_stats()
    {
        $service = app(HealthMetricsService::class);
        $stats = $service->getSystemMetrics();

        $this->assertArrayHasKey('cpu_load_percent', $stats);
        $this->assertArrayHasKey('memory', $stats);
        $this->assertArrayHasKey('disk', $stats);

        $this->assertArrayHasKey('total_bytes', $stats['memory']);
        $this->assertArrayHasKey('used_bytes', $stats['memory']);
        $this->assertArrayHasKey('used_percent', $stats['memory']);

        $this->assertArrayHasKey('total_bytes', $stats['disk']);
        $this->assertArrayHasKey('used_bytes', $stats['disk']);
        $this->assertArrayHasKey('used_percent', $stats['disk']);
    }

    public function test_database_metrics_service_returns_connection_metrics()
    {
        $service = app(DatabaseMetricsService::class);
        $dbMetrics = $service->getDatabaseMetrics();

        $this->assertArrayHasKey('connections', $dbMetrics);
        $this->assertArrayHasKey('slow_queries_count', $dbMetrics);
        $this->assertGreaterThanOrEqual(0, $dbMetrics['connections']);
        $this->assertGreaterThanOrEqual(0, $dbMetrics['slow_queries_count']);
    }

    public function test_queue_metrics_service_returns_redis_stats()
    {
        $service = app(QueueMetricsService::class);
        $queueMetrics = $service->getQueueMetrics();

        $this->assertArrayHasKey('pending_jobs', $queueMetrics);
        $this->assertArrayHasKey('failed_jobs', $queueMetrics);
        $this->assertGreaterThanOrEqual(0, $queueMetrics['pending_jobs']);
        $this->assertGreaterThanOrEqual(0, $queueMetrics['failed_jobs']);
    }

    public function test_business_metrics_service_returns_sales_and_operational_counters()
    {
        $service = app(BusinessMetricsService::class);
        $businessMetrics = $service->getBusinessMetrics();

        $this->assertArrayHasKey('orders_last_hour', $businessMetrics);
        $this->assertArrayHasKey('average_ticket_cents', $businessMetrics);
        $this->assertArrayHasKey('sales_today_cents', $businessMetrics);
        $this->assertArrayHasKey('occupied_tables', $businessMetrics);
        $this->assertArrayHasKey('deliveries_in_progress', $businessMetrics);
        $this->assertArrayHasKey('orders_in_production', $businessMetrics);
    }

    public function test_admin_can_access_dashboard_html_view()
    {
        $this->withoutMiddleware();
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_admin_can_retrieve_json_metrics_endpoint()
    {
        $this->withoutMiddleware();
        $response = $this->get('/admin/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'timestamp',
                'system',
                'database',
                'queue',
                'business',
            ]);
    }
}
