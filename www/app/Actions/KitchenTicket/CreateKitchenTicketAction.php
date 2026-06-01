<?php

declare(strict_types=1);

namespace App\Actions\KitchenTicket;

use App\Enums\KitchenTicketStatusEnum;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CreateKitchenTicketAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Order $order): KitchenTicket
    {
        return DB::transaction(function () use ($order) {
            $ticket = KitchenTicket::create([
                'order_id' => $order->id,
                'status' => KitchenTicketStatusEnum::PENDING,
                'sent_at' => Carbon::now(),
            ]);

            // Registrar log de auditoria
            $this->auditService->log('kitchen.create', [
                'ticket_uuid' => $ticket->uuid,
                'order_uuid' => $order->uuid,
                'company_id' => $order->company_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.kitchen', 'kitchen.created', [
                'uuid' => $ticket->uuid,
                'order_number' => $order->order_number,
                'status' => $ticket->status->value,
                'sent_at' => $ticket->sent_at->toDateTimeString(),
            ]);

            return $ticket;
        });
    }
}
