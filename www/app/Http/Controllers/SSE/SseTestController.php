<?php

namespace App\Http\Controllers\SSE;

use App\Http\Controllers\Controller;
use App\Services\SSE\SsePublisher;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseTestController extends Controller
{
    private SsePublisher $publisher;

    public function __construct(SsePublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Retorna a stream ativa de Server-Sent Events para fins de teste operacional da Fase 1.
     */
    public function stream(): StreamedResponse
    {
        return $this->publisher->subscribe('test.channel', function (SsePublisher $pub) {
            // Aqui poderíamos consultar o Redis ou banco por eventos enfileirados
            // Para o endpoint de teste da Fase 1, apenas mantemos a conexão viva e permitimos heartbeats
        });
    }
}
