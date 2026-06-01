<?php

declare(strict_types=1);

namespace App\Services\DataTables\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent/Query Builder para a listagem.
     */
    public function getQuery(): Builder;

    /**
     * Mapeia as colunas do frontend para colunas do banco com fins de ordenação.
     * Ex: ['name' => 'companies.legal_name']
     */
    public function getSortableColumnsWhitelist(): array;

    /**
     * Retorna os campos permitidos para busca textual global.
     */
    public function getSearchableColumns(): array;

    /**
     * Formata uma linha individual do resultado do banco de dados em um array JSON para o DataTables.
     */
    public function formatRow(mixed $row): array;
}
