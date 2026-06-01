<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Unit\CreateUnitDTO;
use App\DTOs\Unit\UpdateUnitDTO;
use App\Enums\UnitStatusEnum;
use App\Models\CompanyUnit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UnitService
{
    /**
     * Busca uma unidade pelo seu UUID público.
     *
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid): CompanyUnit
    {
        return CompanyUnit::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Retorna todas as unidades ativas de uma empresa.
     */
    public function getActiveUnitsByCompany(int $companyId): Collection
    {
        return CompanyUnit::where('company_id', $companyId)
            ->where('status', UnitStatusEnum::ACTIVE)
            ->get();
    }

    /**
     * Cria e persiste uma nova unidade.
     */
    public function create(CreateUnitDTO $dto): CompanyUnit
    {
        return CompanyUnit::create([
            'company_id' => $dto->company_id,
            'status' => $dto->status,
            'name' => $dto->name,
            'document_number' => $dto->document_number,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'zipcode' => $dto->zipcode,
            'street' => $dto->street,
            'number' => $dto->number,
            'district' => $dto->district,
            'city' => $dto->city,
            'state' => $dto->state,
            'country' => $dto->country,
            'settings_json' => $dto->settings_json,
        ]);
    }

    /**
     * Atualiza os dados de uma unidade existente.
     */
    public function update(CompanyUnit $unit, UpdateUnitDTO $dto): CompanyUnit
    {
        $unit->update(array_filter([
            'name' => $dto->name,
            'document_number' => $dto->document_number,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'zipcode' => $dto->zipcode,
            'street' => $dto->street,
            'number' => $dto->number,
            'district' => $dto->district,
            'city' => $dto->city,
            'state' => $dto->state,
            'country' => $dto->country,
            'settings_json' => $dto->settings_json,
            'status' => $dto->status ?? $unit->status,
        ], fn ($value) => $value !== null));

        return $unit;
    }

    /**
     * Remove uma unidade via soft delete.
     */
    public function delete(CompanyUnit $unit): bool
    {
        return $unit->delete();
    }
}
