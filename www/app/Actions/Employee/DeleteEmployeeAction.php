<?php

declare(strict_types=1);

namespace App\Actions\Employee;

use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteEmployeeAction
{
    public function __construct(
        private readonly EmployeeService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão lógica do funcionário sob transação e auditoria.
     */
    public function execute(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            $before = $employee->toArray();
            
            $this->service->delete($employee);

            $this->auditService->log(
                action: 'employee.delete',
                before: $before,
                after: null,
                context: [
                    'employee_uuid' => $employee->uuid,
                    'company_id' => $employee->company_id,
                ]
            );
        });
    }
}
