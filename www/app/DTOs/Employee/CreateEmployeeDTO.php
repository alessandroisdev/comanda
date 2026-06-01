<?php

declare(strict_types=1);

namespace App\DTOs\Employee;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use Illuminate\Support\Carbon;

class CreateEmployeeDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly ?int $unit_id,
        public readonly string $employee_number,
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $phone = null,
        public readonly ?string $document = null,
        public readonly ?Carbon $birth_date = null,
        public readonly ?Carbon $hire_date = null,
        public readonly EmployeeRoleEnum $role = EmployeeRoleEnum::WAITER,
        public readonly EmployeeStatusEnum $status = EmployeeStatusEnum::ACTIVE
    ) {}

    /**
     * Cria o DTO a partir de um array sanitizado ou request data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            company_id: (int) $data['company_id'],
            unit_id: isset($data['unit_id']) ? (int) $data['unit_id'] : null,
            employee_number: trim($data['employee_number']),
            name: trim($data['name']),
            email: strtolower(trim($data['email'])),
            password: $data['password'],
            phone: isset($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : null,
            document: isset($data['document']) ? preg_replace('/[^0-9]/', '', $data['document']) : null,
            birth_date: !empty($data['birth_date']) ? Carbon::parse($data['birth_date']) : null,
            hire_date: !empty($data['hire_date']) ? Carbon::parse($data['hire_date']) : null,
            role: is_string($data['role']) ? EmployeeRoleEnum::from($data['role']) : $data['role'],
            status: isset($data['status'])
                ? (is_string($data['status']) ? EmployeeStatusEnum::from($data['status']) : $data['status'])
                : EmployeeStatusEnum::ACTIVE
        );
    }
}
