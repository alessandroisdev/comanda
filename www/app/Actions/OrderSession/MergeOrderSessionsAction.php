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

class MergeOrderSessionsAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(OrderSession $sourceSession, OrderSession $targetSession, int $employeeId): OrderSession
    {
        return DB::transaction(function () use ($sourceSession, $targetSession, $employeeId) {

            // Move todos os pedidos da comanda de origem para a de destino
            $sourceSession->orders()->update([
                'session_id' => $targetSession->id,
            ]);

            // Cancela a comanda de origem
            $sourceSession->update([
                'status' => OrderSessionStatusEnum::CANCELLED,
                'closed_by_employee_id' => $employeeId,
                'closed_at' => Carbon::now(),
                'notes' => 'Mesclada com a sessão '.$targetSession->uuid,
            ]);

            // Se a comanda de origem possuir mesa, libera a mesa correspondente
            if ($sourceSession->table_id) {
                $table = Table::find($sourceSession->table_id);
                if ($table) {
                    $table->update(['status' => TableStatusEnum::AVAILABLE]);

                    SseQueueService::publish('admin.tables', 'tables.status_changed', [
                        'uuid' => $table->uuid,
                        'code' => $table->code,
                        'new_status' => TableStatusEnum::AVAILABLE->value,
                    ]);
                }
            }

            // Somar contagem de pessoas na de destino
            $targetSession->people_count += $sourceSession->people_count;
            $targetSession->save();

            // Registrar log de auditoria
            $this->auditService->log('session.merge', [
                'source_session_uuid' => $sourceSession->uuid,
                'target_session_uuid' => $targetSession->uuid,
                'company_id' => $targetSession->company_id,
                'unit_id' => $targetSession->unit_id,
                'merged_by' => $employeeId,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.sessions', 'session.merged', [
                'source_session_uuid' => $sourceSession->uuid,
                'target_session_uuid' => $targetSession->uuid,
            ]);

            return $targetSession;
        });
    }
}
