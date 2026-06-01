<?php

declare(strict_types=1);

namespace App\Http\Requests\Unit;

use App\Enums\UnitStatusEnum;
use App\Models\CompanyUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Determine se o usuário está autorizado a fazer este request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obter as regras de validação aplicáveis ao request.
     */
    public function rules(): array
    {
        $unitUuid = $this->route('unit');
        $unitId = null;

        if ($unitUuid) {
            $unitId = CompanyUnit::where('uuid', $unitUuid)->value('id');
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'document_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('company_units', 'document_number')->ignore($unitId),
            ],
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
