<?php

declare(strict_types=1);

namespace App\Actions\Employee;

use App\DTOs\Employee\CreateEmployeeDTO;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class CreateEmployeeAction
{
    public function __construct(
        private readonly EmployeeService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação de um funcionário sob controle de transação e auditoria.
     */
    public function execute(CreateEmployeeDTO $dto): Employee
    {
        return DB::transaction(function () use ($dto) {
            $employee = $this->service->create($dto);

            $this->auditService->log(
                action: 'employee.create',
                before: null,
                after: $employee->toArray(),
                context: [
                    'employee_uuid' => $employee->uuid,
                    'company_id' => $employee->company_id,
                    'unit_id' => $employee->unit_id,
                    'role' => $employee->role->value,
                ]
            );

            return $employee;
        });
    }
}
