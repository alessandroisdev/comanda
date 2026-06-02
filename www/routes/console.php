<?php

use App\Services\Monitoring\MetricsService;
use App\Services\SSE\SseQueueService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $endTime = time() + 55;
    while (time() < $endTime) {
        try {
            $metrics = app(MetricsService::class)->getFullMetrics();
            SseQueueService::publish('admin.dashboard', 'metrics.updated', $metrics);
        } catch (Throwable $e) {
            // Silencia erros temporários
        }
        sleep(5);
    }
})->everyMinute()->name('broadcast-metrics-sse');
