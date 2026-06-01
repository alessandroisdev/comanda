<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\CompanyStatusEnum;
use App\Models\Company;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class ChangeCompanyStatusAction
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a alteração do status de uma empresa.
     */
    public function execute(Company $company, CompanyStatusEnum $newStatus): Company
    {
        return DB::transaction(function () use ($company, $newStatus) {
            $before = $company->toArray();

            $company->status = $newStatus;
            $company->save();

            $this->auditService->log(
                action: 'company.status_change',
                before: $before,
                after: $company->toArray(),
                context: [
                    'company_uuid' => $company->uuid,
                    'old_status' => $before['status'],
                    'new_status' => $newStatus->value,
                ]
            );

            return $company;
        });
    }
}
