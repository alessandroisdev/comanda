<?php

declare(strict_types=1);

namespace App\Http\Requests\Company;

use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateCompanyRequest extends FormRequest
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
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', new Enum(DocumentTypeEnum::class)],
            'document_number' => ['required', 'string', 'max:30', 'unique:companies,document_number'],
            'email' => ['required', 'email', 'max:150', 'unique:companies,email'],
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
