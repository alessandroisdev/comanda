<?php

declare(strict_types=1);

namespace App\Actions\Table;

use App\Enums\TableStatusEnum;
use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class ChangeTableStatusAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Table $table, TableStatusEnum $status): Table
    {
        return DB::transaction(function () use ($table, $status) {
            $oldStatus = $table->status;
            $table->update(['status' => $status]);

            // Registrar log de auditoria
            $this->auditService->log('table.status_changed', [
                'table_uuid' => $table->uuid,
                'company_id' => $table->company_id,
                'unit_id' => $table->unit_id,
                'old_status' => $oldStatus->value,
                'new_status' => $status->value,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.tables', 'tables.status_changed', [
                'uuid' => $table->uuid,
                'code' => $table->code,
                'old_status' => $oldStatus->value,
                'new_status' => $status->value,
            ]);

            return $table;
        });
    }
}
