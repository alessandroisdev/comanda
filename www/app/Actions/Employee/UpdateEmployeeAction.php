<?php

declare(strict_types=1);

namespace App\Actions\Employee;

use App\DTOs\Employee\UpdateEmployeeDTO;
use App\Models\Employee;
use App\Services\Audit\AuditService;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\DB;

class UpdateEmployeeAction
{
    public function __construct(
        private readonly EmployeeService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização de um funcionário sob transação e auditoria.
     */
    public function execute(Employee $employee, UpdateEmployeeDTO $dto): Employee
    {
        return DB::transaction(function () use ($employee, $dto) {
            $before = $employee->toArray();

            $updatedEmployee = $this->service->update($employee, $dto);

            $this->auditService->log(
                action: 'employee.update',
                before: $before,
                after: $updatedEmployee->toArray(),
                context: [
                    'employee_uuid' => $updatedEmployee->uuid,
                    'company_id' => $updatedEmployee->company_id,
                    'unit_id' => $updatedEmployee->unit_id,
                    'role' => $updatedEmployee->role->value,
                ]
            );

            return $updatedEmployee;
        });
    }
}
