<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Enums\CustomerStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado pela Policy via Gate::authorize
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:customers,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => ['nullable', 'string', 'max:20', 'unique:customers,document'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'status' => ['required', new Enum(CustomerStatusEnum::class)],
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
        $this->merge([
            'marketing_opt_in' => $this->boolean('marketing_opt_in'),
        ]);
    }
}
