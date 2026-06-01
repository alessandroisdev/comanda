<?php

declare(strict_types=1);

namespace App\Services\DataTables;

use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTableQueryService
{
    /**
     * Processa a query do DataTable a partir de uma requisição HTTP e um provedor de dados.
     */
    public function process(Request $request, DataTableProviderInterface $provider, ?callable $queryModifier = null): array
    {
        $query = $provider->getQuery();

        if ($queryModifier !== null) {
            $queryModifier($query);
        }

        // 1. Contagem total sem nenhum filtro aplicado
        $recordsTotal = $query->clone()->count();

        // 2. Aplicar busca global textual se fornecida
        $search = $request->input('search');
        $searchValue = $search['value'] ?? null;
        if (! empty($searchValue)) {
            $searchableColumns = $provider->getSearchableColumns();
            if (! empty($searchableColumns)) {
                $query->where(function (Builder $subQuery) use ($searchableColumns, $searchValue) {
                    foreach ($searchableColumns as $index => $column) {
                        if ($index === 0) {
                            $subQuery->where($column, 'like', '%'.$searchValue.'%');
                        } else {
                            $subQuery->orWhere($column, 'like', '%'.$searchValue.'%');
                        }
                    }
                });
            }
        }

        // 3. Aplicar filtros adicionais do formulário personalizado (opcional)
        $this->applyCustomFilters($request, $query);

        // 4. Contagem filtrada
        $recordsFiltered = $query->clone()->count();

        // 5. Aplicar ordenação segura via whitelist
        $order = $request->input('order');
        if (! empty($order) && is_array($order)) {
            $columns = $request->input('columns');
            $whitelist = $provider->getSortableColumnsWhitelist();

            foreach ($order as $orderRule) {
                $columnIndex = (int) ($orderRule['column'] ?? 0);
                $dir = ($orderRule['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $clientColumnName = $columns[$columnIndex]['data'] ?? null;

                if ($clientColumnName && isset($whitelist[$clientColumnName])) {
                    $dbColumn = $whitelist[$clientColumnName];
                    $query->orderBy($dbColumn, $dir);
                }
            }
        } else {
            // Ordenação padrão decrescente por ID se nenhuma for enviada
            $query->orderBy($query->getModel()->getTable().'.id', 'desc');
        }

        // 6. Aplicar paginação física no banco
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        // Limitar comprimento máximo para evitar esgotamento de memória (DoS)
        if ($length < 1 || $length > 100) {
            $length = 10;
        }

        $records = $query->skip($start)->take($length)->get();

        // 7. Formatar linhas
        $formattedData = [];
        foreach ($records as $record) {
            $formattedData[] = $provider->formatRow($record);
        }

        return [
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData,
        ];
    }

    /**
     * Aplica filtros customizados passados na requisição.
     */
    private function applyCustomFilters(Request $request, Builder $query): void
    {
        // Tratamento genérico de filtros de status
        $status = $request->input('filter_status');
        if (! empty($status)) {
            $query->where($query->getModel()->getTable().'.status', $status);
        }

        // Tratamento genérico de filtros por empresa
        $companyUuid = $request->input('filter_company_uuid');
        if (! empty($companyUuid)) {
            $query->whereHas('company', function (Builder $subQuery) use ($companyUuid) {
                $subQuery->where('uuid', $companyUuid);
            });
        }
    }
}
