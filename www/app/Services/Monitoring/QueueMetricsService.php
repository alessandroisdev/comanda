<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

class QueueMetricsService
{
    /**
     * Retorna o número de jobs pendentes na fila e de falhas.
     */
    public function getQueueMetrics(): array
    {
        return [
            'pending_jobs' => $this->getPendingJobsCount(),
            'failed_jobs' => $this->getFailedJobsCount(),
        ];
    }

    private function getPendingJobsCount(): int
    {
        try {
            return Queue::size();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getFailedJobsCount(): int
    {
        try {
            if (Schema::hasTable('failed_jobs')) {
                return DB::table('failed_jobs')->count();
            }
        } catch (Exception $e) {
            // Silenciar
        }

        return 0;
    }
}
