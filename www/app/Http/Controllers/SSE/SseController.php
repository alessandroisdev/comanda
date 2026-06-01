<?php

declare(strict_types=1);

namespace App\Http\Controllers\SSE;

use App\Http\Controllers\Controller;
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
        });
    }
}
