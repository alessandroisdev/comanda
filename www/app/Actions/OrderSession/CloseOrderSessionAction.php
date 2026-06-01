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

class CloseOrderSessionAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(OrderSession $session, int $employeeId): OrderSession
    {
        return DB::transaction(function () use ($session, $employeeId) {
            $session->update([
                'status' => OrderSessionStatusEnum::CLOSED,
                'closed_by_employee_id' => $employeeId,
                'closed_at' => Carbon::now(),
            ]);

            // Se houver mesa associada, marca a mesa como limpeza
            if ($session->table_id) {
                $table = Table::find($session->table_id);
                if ($table) {
                    $table->update(['status' => TableStatusEnum::CLEANING]);
                    
                    // Publicar SSE de alteração da mesa
                    SseQueueService::publish('admin.tables', 'tables.status_changed', [
                        'uuid' => $table->uuid,
                        'code' => $table->code,
                        'new_status' => TableStatusEnum::CLEANING->value,
                    ]);
                }
            }

            // Registrar log de auditoria
            $this->auditService->log('session.close', [
                'session_uuid' => $session->uuid,
                'company_id' => $session->company_id,
                'unit_id' => $session->unit_id,
                'closed_by' => $employeeId,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.sessions', 'session.closed', [
                'uuid' => $session->uuid,
                'status' => $session->status->value,
                'closed_at' => $session->closed_at->toDateTimeString(),
            ]);

            return $session;
        });
    }
}
