<?php

namespace App\Services\Audit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Registra um evento de auditoria no log estruturado e no banco de dados.
     *
     * @param  string  $action  Chave da ação executada (ex: order.cancel, license.renew)
     * @param  array|null  $before  Payload do estado do recurso antes da modificação
     * @param  array|null  $after  Payload do estado do recurso após a modificação
     * @param  array  $context  Contexto livre adicional da operação
     */
    public function log(string $action, ?array $before = null, ?array $after = null, array $context = []): void
    {
        // Obter ator autenticado em qualquer um dos guards
        $actor = $this->resolveActiveActor();

        $auditPayload = [
            'uuid' => $this->generateUuid(),
            'action' => $action,
            'actor_id' => $actor ? $actor['id'] : null,
            'actor_type' => $actor ? $actor['type'] : 'guest',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'payload_before' => $before,
            'payload_after' => $after,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Gravar nos Logs Estruturados do Sistema (canal audit se disponível)
        Log::channel('daily')->info('AUDIT_LOG', $auditPayload);

        // 2. Gravar no Banco de Dados de Auditoria
        try {
            // Utilizando o Query Builder do Laravel para evitar dependência de Model rígido na fundação
            DB::table('audit_logs')->insert([
                'uuid' => $auditPayload['uuid'],
                'action' => $auditPayload['action'],
                'actor_id' => $auditPayload['actor_id'],
                'actor_type' => $auditPayload['actor_type'],
                'ip_address' => $auditPayload['ip_address'],
                'user_agent' => $auditPayload['user_agent'],
                'payload_before' => $before ? json_encode($before) : null,
                'payload_after' => $after ? json_encode($after) : null,
                'context' => json_encode($context),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fallback silencioso para não interromper a requisição do usuário em falhas de banco
            Log::channel('daily')->error('AUDIT_DATABASE_FAIL', [
                'error' => $e->getMessage(),
                'original_payload' => $auditPayload,
            ]);
        }
    }

    /**
     * Resolve o ator autenticado ativo verificando os guards do sistema.
     */
    private function resolveActiveActor(): ?array
    {
        $guards = ['admin', 'employee', 'customer', 'web', 'api'];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                return [
                    'id' => $user->id,
                    'type' => get_class($user),
                ];
            }
        }

        return null;
    }

    /**
     * Gerador de UUID v4 simples para auditoria offline.
     */
    private function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        );
    }
}
