<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\KitchenTicket\CreateKitchenTicketAction;
use App\Actions\PrintJob\EnqueuePrintJobAction;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class SendOrderToKitchenAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->update(['status' => OrderStatusEnum::SENT_TO_KITCHEN]);

            // Criar ticket de cozinha usando a Action correspondente
            $kitchenAction = app(CreateKitchenTicketAction::class);
            $kitchenAction->execute($order);

            // Gerar job de impressão térmica usando a Action correspondente
            $printAction = app(EnqueuePrintJobAction::class);
            $itemsPayload = [];
            foreach ($order->items as $item) {
                $itemsPayload[] = [
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                ];
            }
            $printAction->execute([
                'company_id' => $order->company_id,
                'unit_id' => $order->unit_id,
                'type' => 'kitchen_ticket',
                'payload' => [
                    'order_number' => $order->order_number,
                    'table_code' => $order->session->table ? $order->session->table->code : 'S/Mesa',
                    'employee' => $order->employee->name,
                    'items' => $itemsPayload,
                ],
            ]);

            // Registrar log de auditoria
            $this->auditService->log('order.send_to_kitchen', [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'company_id' => $order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.orders', 'orders.sent_to_kitchen', [
                'uuid' => $order->uuid,
                'order_number' => $order->order_number,
            ]);

            return $order;
        });
    }
}
