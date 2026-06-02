<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\DB;
use Exception;

class DatabaseMetricsService
{
    /**
     * Retorna conexões ativas e estatísticas de queries no banco de dados.
     */
    public function getDatabaseMetrics(): array
    {
        return [
            'status' => 'up',
            'connections' => $this->getThreadsConnected(),
            'slow_queries_count' => $this->getSlowQueriesCount(),
        ];
    }

    private function getThreadsConnected(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            if (! empty($result) && isset($result[0]->Value)) {
                return (int) $result[0]->Value;
            }
        } catch (Exception $e) {
            // Silenciar e retornar fallback mínimo
        }

        return 1;
    }

    private function getSlowQueriesCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Slow_queries'");
            if (! empty($result) && isset($result[0]->Value)) {
                return (int) $result[0]->Value;
            }
        } catch (Exception $e) {
            // Silenciar e retornar 0 por padrão
        }

        return 0;
    }
}
