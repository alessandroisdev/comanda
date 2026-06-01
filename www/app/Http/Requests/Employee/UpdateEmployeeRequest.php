<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado pela Policy via Gate::authorize
    }

    public function rules(): array
    {
        $employeeUuid = $this->route('employee');

        // Buscar o registro pelo UUID para obter o ID numérico e o company_id associado
        $employee = Employee::where('uuid', $employeeUuid)->firstOrFail();
        $employeeId = $employee->id;
        $companyId = $employee->company_id;

        return [
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('company_units', 'id')->where('company_id', $companyId),
            ],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($employeeId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'document')->ignore($employeeId),
            ],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['nullable', 'date'],
            'role' => ['required', new Enum(EmployeeRoleEnum::class)],
            'status' => ['required', new Enum(EmployeeStatusEnum::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/[^0-9]/', '', (string) $this->input('document')),
            ]);
        }
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9]/', '', (string) $this->input('phone')),
            ]);
        }
    }
}
