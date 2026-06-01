<?php

declare(strict_types=1);

namespace App\Http\Requests\Unit;

use App\Enums\UnitStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateUnitRequest extends FormRequest
{
    /**
     * Determine se o usuário está autorizado a fazer este request.
     */
    public function authorize(): bool
    {
        return true; // Controlado estritamente via Policies no Controller
    }

    /**
     * Obter as regras de validação aplicáveis ao request.
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:30', 'unique:company_units,document_number'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'zipcode' => ['required', 'string', 'max:15'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'district' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:150'],
            'state' => ['required', 'string', 'max:5'],
            'country' => ['nullable', 'string', 'max:100'],
            'settings_json' => ['nullable', 'array'],
            'status' => ['nullable', new Enum(UnitStatusEnum::class)],
        ];
    }
}
