<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Enums\OrderStatusEnum;
use App\Services\Audit\AuditService;
use App\Services\Payment\GatewayManager;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWebhookAction
{
    public function __construct(
        private readonly GatewayManager $gatewayManager,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa o tratamento desacoplado de retornos assíncronos do Gateway.
     */
    public function execute(string $driver, array $payload): void
    {
        $gateway = $this->gatewayManager->driver($driver);
        $response = $gateway->handleWebhook($payload);

        if (!$response->success) {
            Log::warning("[Gateway Webhook] Processamento do webhook falhou para o provedor: {$driver}");
            return;
        }

        DB::transaction(function () use ($response, $driver) {
            // Localiza a ordem de delivery associada à transação
            $deliveryOrder = DeliveryOrder::where('tracking_code', $response->transactionId)->first();

            if ($deliveryOrder) {
                if ($response->status === 'paid') {
                    $deliveryOrder->update(['status' => 'confirmed']);
                    
                    /** @var Order $order */
                    $order = $deliveryOrder->order;
                    $order->update(['status' => OrderStatusEnum::PENDING]); // Pronto para cozinha/PDV

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
