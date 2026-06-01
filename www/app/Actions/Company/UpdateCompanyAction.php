<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTOs\Company\UpdateCompanyDTO;
use App\Models\Company;
use App\Services\Audit\AuditService;
use App\Services\CompanyService;
use Illuminate\Support\Facades\DB;

class UpdateCompanyAction
{
    public function __construct(
        private readonly CompanyService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização de uma empresa.
     */
    public function execute(Company $company, UpdateCompanyDTO $dto): Company
    {
        return DB::transaction(function () use ($company, $dto) {
            $before = $company->toArray();

            $updatedCompany = $this->service->update($company, $dto);

            $this->auditService->log(
                action: 'company.update',
                before: $before,
                after: $updatedCompany->toArray(),
                context: [
                    'company_uuid' => $updatedCompany->uuid,
                    'document_number' => $updatedCompany->document_number,
                ]
            );

            return $updatedCompany;
        });
    }
}
