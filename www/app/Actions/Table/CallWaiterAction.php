<?php

declare(strict_types=1);

namespace App\Actions\Table;

use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;

class CallWaiterAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Executa o chamado de garçom para uma mesa física específica.
     */
    public function execute(Table $table): void
    {
        // Registrar ação operacional na auditoria imutável
        $this->auditService->log('table.waiter_called', [
            'table_uuid' => $table->uuid,
            'table_code' => $table->code,
            'company_id' => $table->company_id,
            'unit_id' => $table->unit_id,
        ]);

        // Publicar evento reativo via Server-Sent Events (SSE)
        SseQueueService::publish('admin.tables', 'waiter.called', [
            'table_uuid' => $table->uuid,
            'table_code' => $table->code,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
