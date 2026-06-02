<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Order\SendOrderToKitchenAction;
use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Services\Audit\AuditService;
use App\Services\Payment\GatewayManager;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWebhookAction
{
    public function __construct(
        private readonly GatewayManager $gatewayManager,
        private readonly AuditService $auditService,
        private readonly SendOrderToKitchenAction $sendOrderToKitchenAction
    ) {}

    /**
     * Executa o tratamento desacoplado de retornos assíncronos do Gateway.
     */
    public function execute(string $driver, array $payload): void
    {
        $gateway = $this->gatewayManager->driver($driver);
        $response = $gateway->handleWebhook($payload);

        if (! $response->success) {
            Log::warning("[Gateway Webhook] Processamento do webhook falhou para o provedor: {$driver}");

            return;
        }

        DB::transaction(function () use ($response, $driver) {
            // Localiza a ordem de delivery associada à transação com lock para concorrência
            $deliveryOrder = DeliveryOrder::where('tracking_code', $response->transactionId)
                ->lockForUpdate()
                ->first();

            if ($deliveryOrder) {
                // Checagem de Idempotência
                if (in_array($deliveryOrder->status, ['confirmed', 'paid'])) {
                    Log::info("[Gateway Webhook] Webhook ignorado por idempotência. Ordem {$deliveryOrder->uuid} já está paga/confirmada.");

                    return;
                }

                if ($response->status === 'paid') {
                    $deliveryOrder->update(['status' => 'confirmed']);

                    /** @var Order $order */
                    $order = $deliveryOrder->order;

                    // Envia o pedido diretamente para a cozinha (cria ticket e altera status para sent_to_kitchen)
                    $this->sendOrderToKitchenAction->execute($order);

                    // Auditoria física do recebimento em total conformidade LGPD
                    $this->auditService->log('payment.webhook_confirmed', [
                        'delivery_order_uuid' => $deliveryOrder->uuid,
                        'transaction_id' => $response->transactionId,
                        'driver' => $driver,
                        'amount' => $deliveryOrder->delivery_fee,
                    ]);

                    // Publicação do evento SSE reativo correspondente da Fase 4
                    SseQueueService::publish('admin.orders', 'order.confirmed', [
                        'order_uuid' => $order->uuid,
                        'order_number' => $order->order_number,
                        'status' => 'confirmed',
                        'timestamp' => now()->toIso8601String(),
                    ]);
                }
            }
        });
    }
}
