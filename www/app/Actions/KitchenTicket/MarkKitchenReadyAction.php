<?php

declare(strict_types=1);

namespace App\Actions\KitchenTicket;

use App\Enums\KitchenTicketStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\KitchenTicket;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarkKitchenReadyAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(KitchenTicket $ticket): KitchenTicket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket->update([
                'status' => KitchenTicketStatusEnum::READY,
                'ready_at' => Carbon::now(),
            ]);

            // Atualiza status do pedido para pronto
            $ticket->order->update(['status' => OrderStatusEnum::READY]);

            // Registrar log de auditoria
            $this->auditService->log('kitchen.mark_ready', [
                'ticket_uuid' => $ticket->uuid,
                'order_uuid' => $ticket->order->uuid,
                'company_id' => $ticket->order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.kitchen', 'kitchen.ready', [
                'uuid' => $ticket->uuid,
                'order_number' => $ticket->order->order_number,
                'status' => $ticket->status->value,
            ]);

            return $ticket;
        });
    }
}
