<?php

declare(strict_types=1);

namespace App\Services\SSE;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SseQueueService
{
    public static function publish(string $channel, string $event, array $data): void
    {
        $key = "sse_events:{$channel}";
        try {
            $events = Cache::get($key, []);
            $events[] = [
                'event' => $event,
                'data' => $data,
                'timestamp' => time(),
            ];
            Cache::put($key, $events, 60);
        } catch (\Throwable $e) {
            // Silencia erro de cache se o Redis estiver offline
        }

        if (! app()->runningUnitTests()) {
            try {
                // Faz o POST para o servidor Node.js dedicado
                $url = 'http://sse-server:8082/publish';

                Http::timeout(1)
                     ->withHeaders(['Content-Type' => 'application/json'])
                     ->post($url, [
                         'channel' => $channel,
                         'event' => $event,
                         'data' => $data,
                     ]);
            } catch (\Throwable $e) {
                // Fallback silencioso para garantir resiliência
                Log::warning("[SSE Server] Falha ao publicar evento no canal '{$channel}': ".$e->getMessage());
            }
        }
    }

    /**
     * Recupera e limpa todos os eventos pendentes de um canal.
     */
    public static function pull(string $channel): array
    {
        $key = "sse_events:{$channel}";
        try {
            $events = Cache::get($key, []);

            if (! empty($events)) {
                Cache::forget($key);
            }

            return $events;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
