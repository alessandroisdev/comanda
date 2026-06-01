<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado pela Policy via Gate::authorize
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('company_units', 'id')->where('company_id', $this->input('company_id')),
            ],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees')->where(fn ($query) => $query->where('company_id', $this->input('company_id'))),
            ],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:employees,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => ['nullable', 'string', 'max:20', 'unique:employees,document'],
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
