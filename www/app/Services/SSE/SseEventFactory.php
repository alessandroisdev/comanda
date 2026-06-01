<?php

namespace App\Services\SSE;

class SseEventFactory
{
    /**
     * Constrói e padroniza o payload JSON a ser enviado via Server-Sent Events.
     *
     * @param  string  $event  Nome do evento (ex: order.created, kitchen.item.ready)
     * @param  array  $data  Dados que compõem o corpo útil do evento
     * @param  string|null  $correlationId  Identificador único de correlação para rastreabilidade
     */
    public static function create(string $event, array $data, ?string $correlationId = null): array
    {
        return [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'correlation_id' => $correlationId ?? self::generateCorrelationId(),
            'event_uuid' => self::generateUuid(),
            'version' => 1,
            'data' => $data,
        ];
    }

    /**
     * Gera um correlation ID padrão se não for fornecido.
     */
    private static function generateCorrelationId(): string
    {
        return 'corr_'.bin2hex(random_bytes(8));
    }

    /**
     * Gera um UUID v4.
     */
    private static function generateUuid(): string
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
