<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\DTOs\Unit\CreateUnitDTO;
use App\Models\CompanyUnit;
use App\Services\UnitService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class CreateUnitAction
{
    public function __construct(
        private readonly UnitService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação de uma filial/unidade sob controle de transação e auditoria.
     */
    public function execute(CreateUnitDTO $dto): CompanyUnit
    {
        return DB::transaction(function () use ($dto) {
            $unit = $this->service->create($dto);

            $this->auditService->log(
                action: 'unit.create',
                before: null,
                after: $unit->toArray(),
                context: [
                    'unit_uuid' => $unit->uuid,
                    'company_id' => $unit->company_id,
                    'name' => $unit->name
                ]
            );

            return $unit;
        });
    }
}
