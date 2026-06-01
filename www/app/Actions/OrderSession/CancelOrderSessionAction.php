<?php

declare(strict_types=1);

namespace App\Actions\OrderSession;

use App\Enums\OrderSessionStatusEnum;
use App\Enums\TableStatusEnum;
use App\Models\OrderSession;
use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CancelOrderSessionAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(OrderSession $session, int $employeeId): OrderSession
    {
        return DB::transaction(function () use ($session, $employeeId) {
            $session->update([
                'status' => OrderSessionStatusEnum::CANCELLED,
                'closed_by_employee_id' => $employeeId,
                'closed_at' => Carbon::now(),
            ]);

            // Cancela todos os pedidos pendentes ou rascunho associados à sessão
            $session->orders()->whereIn('status', ['draft', 'pending'])->update([
                'status' => 'cancelled',
            ]);

            // Se houver mesa associada, libera a mesa como disponível
            if ($session->table_id) {
                $table = Table::find($session->table_id);
                if ($table) {
                    $table->update(['status' => TableStatusEnum::AVAILABLE]);
                    
                    // Publicar SSE de alteração da mesa
                    SseQueueService::publish('admin.tables', 'tables.status_changed', [
                        'uuid' => $table->uuid,
                        'code' => $table->code,
                        'new_status' => TableStatusEnum::AVAILABLE->value,
                    ]);
                }
            }

            // Registrar log de auditoria
            $this->auditService->log('session.cancel', [
                'session_uuid' => $session->uuid,
                'company_id' => $session->company_id,
                'unit_id' => $session->unit_id,
                'cancelled_by' => $employeeId,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.sessions', 'session.cancelled', [
                'uuid' => $session->uuid,
                'status' => $session->status->value,
            ]);

            return $session;
        });
    }
}
