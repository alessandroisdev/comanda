<?php

declare(strict_types=1);

namespace App\Actions\KitchenTicket;

use App\Enums\KitchenTicketStatusEnum;
use App\Models\KitchenTicket;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class CancelKitchenTicketAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(KitchenTicket $ticket): KitchenTicket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket->update([
                'status' => KitchenTicketStatusEnum::CANCELLED,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('kitchen.cancel', [
                'ticket_uuid' => $ticket->uuid,
                'order_uuid' => $ticket->order->uuid,
                'company_id' => $ticket->order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.kitchen', 'kitchen.completed', [
                'uuid' => $ticket->uuid,
                'status' => 'cancelled',
            ]);

            return $ticket;
        });
    }
}
