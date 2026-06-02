<?php

declare(strict_types=1);

namespace App\Services\Logging;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class BaseJsonLogService
{
    abstract protected function getLogFilename(): string;

    /**
     * Escreve uma linha de log estruturada no arquivo de destino JSON correspondente.
     */
    public function write(string $level, string $action, string $message, array $extraContext = []): void
    {
        $logPath = storage_path('logs/'.$this->getLogFilename());

        // Rastreabilidade ponta a ponta via Correlation ID e Request ID
        $correlationId = app()->has('correlation_id') ? app('correlation_id') : (string) Str::uuid();
        $requestId = app()->has('request_id') ? app('request_id') : (string) Str::uuid();

        // Escopo multi-tenant e usuário
        $userId = Auth::id();
        $tenantId = null;
        $unitId = null;

        $user = Auth::user();
        if ($user) {
            $tenantId = $user->company_id ?? null;
            $unitId = $user->unit_id ?? null;
        }

        $request = request();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Estrutura em conformidade com as regras de Observabilidade da Fase 6
        $payload = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'level' => strtoupper($level),
            'action' => $action,
            'message' => $message,
            'correlation_id' => $correlationId,
            'request_id' => $requestId,
            'tenant' => $tenantId,
            'unit' => $unitId,
            'user' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'context' => $this->sanitizeContext($extraContext),
        ];

        $logLine = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";

        // Assegurar diretório do log
        $dir = dirname($logPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($logPath, $logLine, FILE_APPEND);
    }

    /**
     * Limpa dados extremamente confidenciais e pessoais de acordo com a LGPD.
     */
    private function sanitizeContext(array $context): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'secret',
            'private_key',
            'key_data',
            'signature',
            'credit_card',
            'card_number',
            'cvv',
        ];

        foreach ($context as $key => $val) {
            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $context[$key] = '[FILTERED]';
            } elseif (is_array($val)) {
                $context[$key] = $this->sanitizeContext($val);
            }
        }

        return $context;
    }
}
