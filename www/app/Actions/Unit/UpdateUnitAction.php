<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\DTOs\Unit\UpdateUnitDTO;
use App\Models\CompanyUnit;
use App\Services\UnitService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class UpdateUnitAction
{
    public function __construct(
        private readonly UnitService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização de uma unidade.
     */
    public function execute(CompanyUnit $unit, UpdateUnitDTO $dto): CompanyUnit
    {
        return DB::transaction(function () use ($unit, $dto) {
            $before = $unit->toArray();

            $updatedUnit = $this->service->update($unit, $dto);

            $this->auditService->log(
                action: 'unit.update',
                before: $before,
                after: $updatedUnit->toArray(),
                context: [
                    'unit_uuid' => $updatedUnit->uuid,
                    'company_id' => $updatedUnit->company_id,
                    'name' => $updatedUnit->name
                ]
            );

            return $updatedUnit;
        });
    }
}
