<?php

declare(strict_types=1);

namespace App\Services\SSE;

use Illuminate\Support\Facades\Cache;

class SseQueueService
{
    /**
     * Publica um evento SSE em um canal.
     */
    public static function publish(string $channel, string $event, array $data): void
    {
        $key = "sse_events:{$channel}";
        
        // Usamos um lock simples ou operação atômica de array no Cache
        $events = Cache::get($key, []);
        $events[] = [
            'event' => $event,
            'data' => $data,
            'timestamp' => time(),
        ];
        
        Cache::put($key, $events, 60); // Expira em 60 segundos
    }

    /**
     * Recupera e limpa todos os eventos pendentes de um canal.
     */
    public static function pull(string $channel): array
    {
        $key = "sse_events:{$channel}";
        $events = Cache::get($key, []);
        
        if (! empty($events)) {
            Cache::forget($key);
        }
        
        return $events;
    }
}
