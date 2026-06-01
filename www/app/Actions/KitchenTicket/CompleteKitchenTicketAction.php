<?php

declare(strict_types=1);

namespace App\Actions\KitchenTicket;

use App\Enums\KitchenTicketStatusEnum;
use App\Models\KitchenTicket;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CompleteKitchenTicketAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(KitchenTicket $ticket): KitchenTicket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket->update([
                'status' => KitchenTicketStatusEnum::COMPLETED,
                'completed_at' => Carbon::now(),
            ]);

            // Atualiza status do pedido para entregue
            $ticket->order->update(['status' => \App\Enums\OrderStatusEnum::DELIVERED]);

            // Registrar log de auditoria
            $this->auditService->log('kitchen.complete', [
                'ticket_uuid' => $ticket->uuid,
                'order_uuid' => $ticket->order->uuid,
                'company_id' => $ticket->order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.kitchen', 'kitchen.completed', [
                'uuid' => $ticket->uuid,
                'order_number' => $ticket->order->order_number,
                'status' => $ticket->status->value,
            ]);

            return $ticket;
        });
    }
}
