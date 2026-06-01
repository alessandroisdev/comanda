<?php

declare(strict_types=1);

namespace App\Http\Requests\Company;

use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCompanyRequest extends FormRequest
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
        $companyUuid = $this->route('company');
        $companyId = null;

        if ($companyUuid) {
            $companyId = Company::where('uuid', $companyUuid)->value('id');
        }

        return [
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', new Enum(DocumentTypeEnum::class)],
            'document_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('companies', 'document_number')->ignore($companyId),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('companies', 'email')->ignore($companyId),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:10'],
            'language' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'string', 'max:255'],
            'settings_json' => ['nullable', 'array'],
            'status' => ['nullable', new Enum(CompanyStatusEnum::class)],
        ];
    }
}
