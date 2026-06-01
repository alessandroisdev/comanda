<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\DataInventory;

class DataInventoryService
{
    /**
     * Adiciona um novo mapeamento de dados pessoais ao inventário geral.
     */
    public function registerItem(array $data): DataInventory
    {
        return DataInventory::create([
            'data_name' => $data['data_name'],
            'data_category' => $data['data_category'],
            'processing_purpose' => $data['processing_purpose'],
            'legal_basis' => $data['legal_basis'],
            'data_subject_type' => $data['data_subject_type'],
            'table_name' => $data['table_name'] ?? null,
            'column_name' => $data['column_name'] ?? null,
            'retention_period' => $data['retention_period'] ?? null,
            'security_measures' => $data['security_measures'] ?? null,
        ]);
    }
}
