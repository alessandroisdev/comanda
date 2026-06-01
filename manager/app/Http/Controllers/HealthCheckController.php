<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    /**
     * Retorna o status de saúde da infraestrutura do Manager (Banco de Dados e Redis).
     */
    public function check(): JsonResponse
    {
        $dbHealthy = $this->checkDatabase();
        $redisHealthy = $this->checkRedis();

        $isHealthy = $dbHealthy && $redisHealthy;

        return response()->json([
            'success' => $isHealthy,
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbHealthy ? 'up' : 'down',
                'redis' => $redisHealthy ? 'up' : 'down',
            ],
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Verifica conexão com o banco de dados.
     */
    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verifica conexão com o Redis.
     */
    private function checkRedis(): bool
    {
        try {
            Redis::connection()->ping();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
