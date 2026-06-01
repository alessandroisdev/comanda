<?php

namespace App\Http\Controllers;

use App\Services\Licensing\LicenseManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    private LicenseManager $licenseManager;

    public function __construct(LicenseManager $licenseManager)
    {
        $this->licenseManager = $licenseManager;
    }

    /**
     * Retorna a integridade e saúde geral de todos os serviços essenciais.
     */
    public function check(): JsonResponse
    {
        $services = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'license' => $this->checkLicense(),
            'storage' => $this->checkStorage(),
        ];

        // A saúde física da infraestrutura está saudável se o banco, Redis e Storage estão UP.
        // O status da licença inativa/inválida apenas bloqueia acessos de negócios, mas não derruba a infraestrutura do container.
        $isHealthy = ! in_array('down', array_column($services, 'status'));

        return response()->json([
            'success' => $isHealthy,
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'services' => $services,
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Liveness Probe - Apenas indica se o container do PHP está rodando.
     */
    public function liveness(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Readiness Probe - Indica se a aplicação está pronta para receber conexões.
     */
    public function readiness(): JsonResponse
    {
        $dbHealthy = $this->checkDatabase()['status'] === 'up';

        return response()->json([
            'success' => $dbHealthy,
            'status' => $dbHealthy ? 'ready' : 'not_ready',
            'timestamp' => now()->toIso8601String(),
        ], $dbHealthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'up', 'details' => 'Database connection successful.'];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();

            return ['status' => 'up', 'details' => 'Redis connection successful.'];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkLicense(): array
    {
        try {
            $status = $this->licenseManager->getStatus();

            return [
                'status' => $status->value,
                'details' => $status->isActive() ? 'License is active.' : 'License is inoperative.',
            ];
        } catch (Exception $e) {
            return ['status' => 'invalid', 'details' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path('app');

        return [
            'status' => is_writable($path) ? 'up' : 'down',
            'details' => is_writable($path) ? 'Storage path is writable.' : 'Storage path is not writable.',
        ];
    }
}
