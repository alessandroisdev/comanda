<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Enums\CustomerStatusEnum;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado pela Policy via Gate::authorize
    }

    public function rules(): array
    {
        $customerUuid = $this->route('customer');
        $customer = Customer::where('uuid', $customerUuid)->firstOrFail();
        $customerId = $customer->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'document' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'document')->ignore($customerId),
            ],
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
