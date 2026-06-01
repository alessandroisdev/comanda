<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Services\CompanyService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteCompanyAction
{
    public function __construct(
        private readonly CompanyService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão de uma empresa.
     */
    public function execute(Company $company): bool
    {
        return DB::transaction(function () use ($company) {
            $before = $company->toArray();
            $uuid = $company->uuid;
            $doc = $company->document_number;

            $result = $this->service->delete($company);

            $this->auditService->log(
                action: 'company.delete',
                before: $before,
                after: null,
                context: [
                    'company_uuid' => $uuid,
                    'document_number' => $doc
                ]
            );

            return $result;
        });
    }
}
