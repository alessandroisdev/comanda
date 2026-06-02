<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

class MetricsService
{
    private HealthMetricsService $health;

    private DatabaseMetricsService $database;

    private QueueMetricsService $queue;

    private BusinessMetricsService $business;

    public function __construct(
        HealthMetricsService $health,
        DatabaseMetricsService $database,
        QueueMetricsService $queue,
        BusinessMetricsService $business
    ) {
        $this->health = $health;
        $this->database = $database;
        $this->queue = $queue;
        $this->business = $business;
    }

    /**
     * Retorna o agregado completo de todas as métricas do sistema.
     */
    public function getFullMetrics(?int $companyId = null, ?int $unitId = null): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'system' => $this->health->getSystemMetrics(),
            'database' => $this->database->getDatabaseMetrics(),
            'queue' => $this->queue->getQueueMetrics(),
            'business' => $this->business->getBusinessMetrics($companyId, $unitId),
        ];
    }
}
