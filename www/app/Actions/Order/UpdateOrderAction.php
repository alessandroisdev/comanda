<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class UpdateOrderAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order->update([
                'discount_cents' => $data['discount_cents'] ?? $order->discount_cents,
                'notes' => $data['notes'] ?? $order->notes,
            ]);

            // Recalcula totais usando a action de recalcular
            $recalculator = app(\App\Actions\OrderItem\RecalculateOrderTotalsAction::class);
            $order = $recalculator->execute($order);

            // Registrar log de auditoria
            $this->auditService->log('order.update', [
                'order_uuid' => $order->uuid,
                'discount_cents' => $order->discount_cents,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.orders', 'orders.updated', [
                'uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'total_cents' => $order->total_cents,
            ]);

            return $order;
        });
    }
}
