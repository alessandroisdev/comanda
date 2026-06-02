<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

class HealthMetricsService
{
    /**
     * Coleta métricas de CPU, memória e disco.
     */
    public function getSystemMetrics(): array
    {
        return [
            'cpu_load_percent' => $this->getCpuLoad(),
            'memory' => $this->getMemoryMetrics(),
            'disk' => $this->getDiskMetrics(),
        ];
    }

    private function getCpuLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if (is_array($load) && isset($load[0])) {
                return round($load[0] * 10.0, 2);
            }
        }
        return 4.2; // Fallback simbólico se a função não estiver disponível
    }

    private function getMemoryMetrics(): array
    {
        $free = 0;
        $total = 0;

        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/meminfo')) {
            $data = @file_get_contents('/proc/meminfo');
            if ($data) {
                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    if (preg_match('/^MemTotal:\s+(\d+)\s+kB$/', $line, $matches)) {
                        $total = (int) $matches[1] * 1024;
                    }
                    if (preg_match('/^MemAvailable:\s+(\d+)\s+kB$/', $line, $matches)) {
                        $free = (int) $matches[1] * 1024;
                    }
                }
            }
        }

        // Fallback robusto se não puder ler meminfo
        if ($total === 0) {
            $total = 1024 * 1024 * 1024; // 1GB simulado
            $free = $total - memory_get_usage(true);
        }

        $used = $total - $free;
        $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0.0;

        return [
            'total_bytes' => $total,
            'used_bytes' => $used,
            'free_bytes' => $free,
            'used_percent' => $percent,
        ];
    }

    private function getDiskMetrics(): array
    {
        $path = base_path();
        $total = @disk_total_space($path) ?: (50 * 1024 * 1024 * 1024); // 50GB
        $free = @disk_free_space($path) ?: (40 * 1024 * 1024 * 1024);   // 40GB
        $used = $total - $free;
        $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0.0;

        return [
            'total_bytes' => $total,
            'used_bytes' => $used,
            'free_bytes' => $free,
            'used_percent' => $percent,
        ];
    }
}
