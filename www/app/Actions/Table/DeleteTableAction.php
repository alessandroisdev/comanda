<?php

declare(strict_types=1);

namespace App\Actions\Table;

use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class DeleteTableAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Table $table): void
    {
        DB::transaction(function () use ($table) {
            $table->delete();

            // Registrar log de auditoria
            $this->auditService->log('table.delete', [
                'table_uuid' => $table->uuid,
                'company_id' => $table->company_id,
                'unit_id' => $table->unit_id,
                'code' => $table->code,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.tables', 'tables.updated', [
                'uuid' => $table->uuid,
                'status' => 'deleted',
            ]);
        });
    }
}
