<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTOs\Company\CreateCompanyDTO;
use App\Models\Company;
use App\Services\Audit\AuditService;
use App\Services\CompanyService;
use Illuminate\Support\Facades\DB;

class CreateCompanyAction
{
    public function __construct(
        private readonly CompanyService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação de uma empresa sob controle de transação e auditoria.
     */
    public function execute(CreateCompanyDTO $dto): Company
    {
        return DB::transaction(function () use ($dto) {
            $company = $this->service->create($dto);

            // Gravar nos registros estruturados de auditoria
            $this->auditService->log(
                action: 'company.create',
                before: null,
                after: $company->toArray(),
                context: [
                    'company_uuid' => $company->uuid,
                    'document_number' => $company->document_number,
                ]
            );

            return $company;
        });
    }
}
