<?php

declare(strict_types=1);

namespace App\Actions\Table;

use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;

class RequestBillAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Executa a solicitação de fechamento da conta de mesa.
     */
    public function execute(Table $table): void
    {
        // Registrar log de auditoria imutável
        $this->auditService->log('table.bill_requested', [
            'table_uuid' => $table->uuid,
            'table_code' => $table->code,
            'company_id' => $table->company_id,
            'unit_id' => $table->unit_id,
        ]);

        // Publicar evento reativo no SSE
        SseQueueService::publish('admin.tables', 'bill.requested', [
            'table_uuid' => $table->uuid,
            'table_code' => $table->code,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
