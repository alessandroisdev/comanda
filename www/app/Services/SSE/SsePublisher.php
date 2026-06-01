<?php

namespace App\Services\SSE;

use Closure;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SsePublisher
{
    /**
     * Gera e publica um StreamedResponse do Symfony de forma contínua para o cliente.
     *
     * @param  string  $channel  Nome do canal ao qual a conexão se associa
     * @param  Closure  $eventSource  Closure que recebe o publicador e retorna/dispara eventos adicionais
     */
    public function subscribe(string $channel, Closure $eventSource): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($eventSource, $channel) {
            // Desativar limites de tempo de execução do script para conexões SSE duradouras
            set_time_limit(0);

            // Garantir que todas as saídas de buffer sejam limpas
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            $lastHeartbeat = time();
            $heartbeatInterval = 15; // 15 segundos conforme diretrizes de SSE

            // Envia o primeiro evento de conexão estabelecida
            $this->sendEvent('connection.established', [
                'channel' => $channel,
                'status' => 'connected',
            ]);

            // Loop infinito da stream aberta
            while (true) {
                // Verificar se a conexão do cliente foi encerrada (aborta o loop)
                if (connection_aborted()) {
                    break;
                }

                // Chamar a Closure do usuário para verificar e emitir novos eventos de negócio
                $eventSource($this);

                // Gerenciar o envio periódico de batimentos cardíacos (Heartbeat) para manter a conexão ativa
                if ((time() - $lastHeartbeat) >= $heartbeatInterval) {
                    $this->sendEvent('heartbeat', ['ping' => time()]);
                    $lastHeartbeat = time();
                }

                // Dormir por 1 segundo antes de iterar para evitar sobrecarga de processamento
                sleep(1);
            }
        });

        // Configurar cabeçalhos obrigatórios para que o navegador reconheça e trate como stream de SSE sem buffering
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, private');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Desativar buffering de proxy reverso/Nginx

        return $response;
    }

    /**
     * Formata e envia uma linha individual de evento estruturado SSE para a saída.
     */
    public function sendEvent(string $event, array $data, ?string $correlationId = null): void
    {
        $payload = SseEventFactory::create($event, $data, $correlationId);

        echo "event: {$event}\n";
        echo 'data: '.json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n\n";

        // Forçar a liberação dos buffers de saída para o cliente final receber de imediato
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
