<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Licensing\LicenseManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class HealthCheckController extends Controller
{
    private LicenseManager $licenseManager;

    public function __construct(LicenseManager $licenseManager)
    {
        $this->licenseManager = $licenseManager;
    }

    /**
     * Retorna a integridade geral antiga (retrocompatibilidade)
     */
    public function check(): JsonResponse
    {
        return $this->full();
    }

    /**
     * Liveness Probe - Apenas indica se o container do PHP está rodando.
     * Endpoint: /api/health/live
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Readiness Probe - Indica se a aplicação está pronta para receber conexões.
     * Endpoint: /api/health/ready
     */
    public function ready(): JsonResponse
    {
        $db = $this->checkDatabase();
        $redis = $this->checkRedis();
        $cache = $this->checkCache();

        $isReady = $db['status'] === 'up' && $redis['status'] === 'up' && $cache['status'] === 'up';

        return response()->json([
            'success' => $isReady,
            'status' => $isReady ? 'ready' : 'not_ready',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $db,
                'redis' => $redis,
                'cache' => $cache,
            ],
        ], $isReady ? 200 : 503);
    }

    /**
     * Full Diagnostics Probe - Diagnóstico completo de subsistemas.
     * Endpoint: /api/health/full
     */
    public function full(): JsonResponse
    {
        $services = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'license' => $this->checkLicense(),
            'sse' => $this->checkSse(),
            'printing' => $this->checkPrinting(),
            'cache' => $this->checkCache(),
            'pwa' => $this->checkPwa(),
        ];

        $isHealthy = ! in_array('down', array_column($services, 'status'));

        return response()->json([
            'success' => $isHealthy,
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'services' => $services,
        ], $isHealthy ? 200 : 503);
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

    private function checkQueue(): array
    {
        try {
            Queue::size();

            return ['status' => 'up', 'details' => 'Queue is operational.'];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path('app');
        $isWritable = is_writable($path);

        return [
            'status' => $isWritable ? 'up' : 'down',
            'details' => $isWritable ? 'Storage path is writable.' : 'Storage path is not writable.',
        ];
    }

    private function checkLicense(): array
    {
        try {
            $status = $this->licenseManager->getStatus();

            return [
                'status' => $status->isActive() ? 'up' : 'down',
                'details' => $status->isActive() ? 'License is active.' : "License status: {$status->value}",
            ];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkSse(): array
    {
        try {
            Redis::connection()->ping();

            return ['status' => 'up', 'details' => 'SSE channel is operational via Redis pub/sub.'];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkPrinting(): array
    {
        try {
            if (Schema::hasTable('print_jobs')) {
                $pending = DB::table('print_jobs')->where('status', 'pending')->count();

                return ['status' => 'up', 'details' => "Print table exists. Pending print jobs: {$pending}"];
            }

            return ['status' => 'down', 'details' => 'Print table does not exist.'];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'healthcheck_cache_test_'.time();
            Cache::put($key, 'ok', 5);
            $value = Cache::get($key);
            $healthy = $value === 'ok';
            Cache::forget($key);

            return [
                'status' => $healthy ? 'up' : 'down',
                'details' => $healthy ? 'Cache read/write test successful.' : 'Cache read/write test failed.',
            ];
        } catch (Exception $e) {
            return ['status' => 'down', 'details' => $e->getMessage()];
        }
    }

    private function checkPwa(): array
    {
        $manifestExists = file_exists(public_path('manifest.json'));
        $swExists = file_exists(public_path('sw.js'));
        $healthy = $manifestExists && $swExists;

        return [
            'status' => $healthy ? 'up' : 'down',
            'details' => $healthy ? 'PWA assets (manifest.json, sw.js) are present.' : 'PWA assets are missing.',
        ];
    }
}
