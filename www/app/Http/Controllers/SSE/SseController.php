<?php

declare(strict_types=1);

namespace App\Http\Controllers\SSE;

use App\Http\Controllers\Controller;
use App\Services\Monitoring\MetricsService;
use App\Services\SSE\SsePublisher;
use App\Services\SSE\SseQueueService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseController extends Controller
{
    public function __construct(private readonly SsePublisher $publisher) {}

    /**
     * Inicia a stream SSE reativa para um canal específico.
     */
    public function stream(string $channel): StreamedResponse
    {
        return $this->publisher->subscribe($channel, function (SsePublisher $pub) use ($channel) {
            $events = SseQueueService::pull($channel);
            foreach ($events as $e) {
                $pub->sendEvent($e['event'], $e['data']);
            }

            if ($channel === 'admin.dashboard') {
                static $lastSent = 0;
                $now = time();
                if ($now - $lastSent >= 5) {
                    try {
                        $metrics = app(MetricsService::class)->getFullMetrics();
                        $pub->sendEvent('metrics.updated', $metrics);
                    } catch (\Exception $e) {
                        // Silencia para evitar quebrar o stream por instabilidade temporária
                    }
                    $lastSent = $now;
                }
            }
        });
    }
}
