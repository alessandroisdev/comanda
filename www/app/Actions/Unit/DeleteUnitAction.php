<?php

declare(strict_types=1);

namespace App\Actions\Unit;

use App\Models\CompanyUnit;
use App\Services\UnitService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteUnitAction
{
    public function __construct(
        private readonly UnitService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão de uma unidade.
     */
    public function execute(CompanyUnit $unit): bool
    {
        return DB::transaction(function () use ($unit) {
            $before = $unit->toArray();
            $uuid = $unit->uuid;
            $name = $unit->name;
            $companyId = $unit->company_id;

            $result = $this->service->delete($unit);

            $this->auditService->log(
                action: 'unit.delete',
                before: $before,
                after: null,
                context: [
                    'unit_uuid' => $uuid,
                    'company_id' => $companyId,
                    'name' => $name
                ]
            );

            return $result;
        });
    }
}
