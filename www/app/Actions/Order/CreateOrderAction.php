<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            
            // Gerar número de pedido único por unidade
            $orderNumber = 'PED-' . str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'company_id' => $data['company_id'],
                'unit_id' => $data['unit_id'],
                'session_id' => $data['session_id'],
                'employee_id' => $data['employee_id'],
                'order_number' => $orderNumber,
                'status' => OrderStatusEnum::DRAFT,
                'subtotal_cents' => 0,
                'discount_cents' => $data['discount_cents'] ?? 0,
                'total_cents' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('order.create', [
                'order_uuid' => $order->uuid,
                'session_uuid' => $order->session->uuid,
                'order_number' => $order->order_number,
                'company_id' => $order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.orders', 'orders.created', [
                'uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
            ]);

            return $order;
        });
    }
}
