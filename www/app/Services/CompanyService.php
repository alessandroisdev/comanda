<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Company\CreateCompanyDTO;
use App\DTOs\Company\UpdateCompanyDTO;
use App\Models\Company;
use App\Enums\CompanyStatusEnum;
use Illuminate\Database\Eloquent\Collection;

class CompanyService
{
    /**
     * Busca uma empresa pelo seu UUID público.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByUuid(string $uuid): Company
    {
        return Company::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Retorna todas as empresas ativas.
     */
    public function getActiveCompanies(): Collection
    {
        return Company::where('status', CompanyStatusEnum::ACTIVE)->get();
    }

    /**
     * Cria e persiste uma nova empresa a partir do DTO correspondente.
     */
    public function create(CreateCompanyDTO $dto): Company
    {
        return Company::create([
            'status' => $dto->status,
            'legal_name' => $dto->legal_name,
            'trade_name' => $dto->trade_name,
            'document_type' => $dto->document_type,
            'document_number' => $dto->document_number,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'timezone' => $dto->timezone,
            'currency' => $dto->currency,
            'language' => $dto->language,
            'logo' => $dto->logo,
            'settings_json' => $dto->settings_json,
        ]);
    }

    /**
     * Atualiza os dados de uma empresa existente a partir do DTO correspondente.
     */
    public function update(Company $company, UpdateCompanyDTO $dto): Company
    {
        $company->update(array_filter([
            'legal_name' => $dto->legal_name,
            'trade_name' => $dto->trade_name,
            'document_type' => $dto->document_type,
            'document_number' => $dto->document_number,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'timezone' => $dto->timezone,
            'currency' => $dto->currency,
            'language' => $dto->language,
            'logo' => $dto->logo,
            'settings_json' => $dto->settings_json,
            'status' => $dto->status ?? $company->status,
        ], fn ($value) => $value !== null));

        return $company;
    }

    /**
     * Remove fisicamente ou via soft delete uma empresa do sistema.
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }
}
