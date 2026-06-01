<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Employee\CreateEmployeeDTO;
use App\DTOs\Employee\UpdateEmployeeDTO;
use App\Models\Employee;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeService
{
    /**
     * Busca um funcionário pelo seu UUID público.
     *
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid): Employee
    {
        return Employee::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Cria e persiste um novo funcionário a partir do DTO correspondente.
     */
    public function create(CreateEmployeeDTO $dto): Employee
    {
        return Employee::create([
            'company_id' => $dto->company_id,
            'unit_id' => $dto->unit_id,
            'employee_number' => $dto->employee_number,
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password, // A senha será hasheada via cast no Model
            'phone' => $dto->phone,
            'document' => $dto->document,
            'birth_date' => $dto->birth_date,
            'hire_date' => $dto->hire_date,
            'role' => $dto->role,
            'status' => $dto->status,
        ]);
    }

    /**
     * Atualiza os dados de um funcionário existente a partir do DTO correspondente.
     */
    public function update(Employee $employee, UpdateEmployeeDTO $dto): Employee
    {
        $data = array_filter([
            'unit_id' => $dto->unit_id,
            'employee_number' => $dto->employee_number,
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'document' => $dto->document,
            'birth_date' => $dto->birth_date,
            'hire_date' => $dto->hire_date,
            'role' => $dto->role,
            'status' => $dto->status,
        ], fn ($value) => $value !== null);

        // Se passar nova senha, atualiza.
        if ($dto->password !== null) {
            $data['password'] = $dto->password;
        }

        // Se unit_id for explicitado como nulo na requisição (ou se não for passado no DTO),
        // no DTO do Update nós permitimos nulo caso seja desvinculado de unidade física.
        $data['unit_id'] = $dto->unit_id;

        $employee->update($data);

        return $employee;
    }

    /**
     * Remove via soft delete um funcionário do sistema.
     */
    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
