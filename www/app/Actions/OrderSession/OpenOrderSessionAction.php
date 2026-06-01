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

class OpenOrderSessionAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(array $data): OrderSession
    {
        return DB::transaction(function () use ($data) {
            $session = OrderSession::create([
                'company_id' => $data['company_id'],
                'unit_id' => $data['unit_id'],
                'table_id' => $data['table_id'] ?? null,
                'opened_by_employee_id' => $data['opened_by_employee_id'],
                'status' => OrderSessionStatusEnum::OPEN,
                'opened_at' => Carbon::now(),
                'people_count' => $data['people_count'] ?? 1,
                'notes' => $data['notes'] ?? null,
            ]);

            // Se houver mesa associada, marca a mesa como ocupada
            if ($session->table_id) {
                $table = Table::find($session->table_id);
                if ($table) {
                    $table->update(['status' => TableStatusEnum::OCCUPIED]);

                    // Publicar SSE de alteração da mesa
                    SseQueueService::publish('admin.tables', 'tables.status_changed', [
                        'uuid' => $table->uuid,
                        'code' => $table->code,
                        'new_status' => TableStatusEnum::OCCUPIED->value,
                    ]);
                }
            }

            // Registrar log de auditoria
            $this->auditService->log('session.open', [
                'session_uuid' => $session->uuid,
                'company_id' => $session->company_id,
                'unit_id' => $session->unit_id,
                'table_id' => $session->table_id,
                'opened_by' => $session->opened_by_employee_id,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.sessions', 'session.opened', [
                'uuid' => $session->uuid,
                'status' => $session->status->value,
                'table_id' => $session->table_id,
                'opened_at' => $session->opened_at->toDateTimeString(),
            ]);

            return $session;
        });
    }
}
