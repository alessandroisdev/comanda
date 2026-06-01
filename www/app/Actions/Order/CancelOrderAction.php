<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->update(['status' => OrderStatusEnum::CANCELLED]);

            // Cancela ticket de cozinha associado
            if ($order->kitchenTicket) {
                $order->kitchenTicket->update(['status' => \App\Enums\KitchenTicketStatusEnum::CANCELLED]);
            }

            // Registrar log de auditoria
            $this->auditService->log('order.cancel', [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'company_id' => $order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.orders', 'orders.cancelled', [
                'uuid' => $order->uuid,
                'order_number' => $order->order_number,
            ]);

            return $order;
        });
    }
}
