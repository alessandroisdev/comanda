<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Obter ou gerar o Correlation ID (rastreabilidade global)
        $correlationId = $request->header('X-Correlation-ID') ?: (string) Str::uuid();
        app()->instance('correlation_id', $correlationId);

        // 2. Gerar o Request ID (identificação única local deste request)
        $requestId = (string) Str::uuid();
        app()->instance('request_id', $requestId);

        // 3. Processar a requisição
        $response = $next($request);

        // 4. Anexar o Correlation ID à resposta HTTP para o cliente
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
