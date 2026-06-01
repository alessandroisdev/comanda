<?php

declare(strict_types=1);

namespace App\Actions\Table;

use App\DTOs\Table\CreateTableDTO;
use App\Models\Table;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Facades\DB;

class CreateTableAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(CreateTableDTO $dto): Table
    {
        return DB::transaction(function () use ($dto) {
            $table = Table::create([
                'company_id' => $dto->company_id,
                'unit_id' => $dto->unit_id,
                'code' => $dto->code,
                'name' => $dto->name,
                'capacity' => $dto->capacity,
                'sector' => $dto->sector,
                'status' => $dto->status,
                'sort_order' => $dto->sort_order,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('table.create', [
                'table_uuid' => $table->uuid,
                'company_id' => $table->company_id,
                'unit_id' => $table->unit_id,
                'code' => $table->code,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.tables', 'tables.created', [
                'uuid' => $table->uuid,
                'code' => $table->code,
                'name' => $table->name,
                'status' => $table->status->value,
                'sector' => $table->sector,
            ]);

            return $table;
        });
    }
}
