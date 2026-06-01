<?php

declare(strict_types=1);

namespace App\Actions\OrderSession;

use App\Enums\TableStatusEnum;
use App\Models\OrderSession;
use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class TransferTableAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(OrderSession $session, Table $newTable): OrderSession
    {
        return DB::transaction(function () use ($session, $newTable) {
            $oldTableId = $session->table_id;

            // Atualiza a sessão operacional com a nova mesa
            $session->update(['table_id' => $newTable->id]);

            // Libera a mesa antiga se houver
            if ($oldTableId) {
                $oldTable = Table::find($oldTableId);
                if ($oldTable) {
                    $oldTable->update(['status' => TableStatusEnum::AVAILABLE]);

                    SseQueueService::publish('admin.tables', 'tables.status_changed', [
                        'uuid' => $oldTable->uuid,
                        'code' => $oldTable->code,
                        'new_status' => TableStatusEnum::AVAILABLE->value,
                    ]);
                }
            }

            // Ocupa a nova mesa
            $newTable->update(['status' => TableStatusEnum::OCCUPIED]);

            SseQueueService::publish('admin.tables', 'tables.status_changed', [
                'uuid' => $newTable->uuid,
                'code' => $newTable->code,
                'new_status' => TableStatusEnum::OCCUPIED->value,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('session.transfer', [
                'session_uuid' => $session->uuid,
                'company_id' => $session->company_id,
                'unit_id' => $session->unit_id,
                'old_table_id' => $oldTableId,
                'new_table_id' => $newTable->id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.sessions', 'session.transferred', [
                'uuid' => $session->uuid,
                'old_table_id' => $oldTableId,
                'new_table_id' => $newTable->id,
            ]);

            return $session;
        });
    }
}
