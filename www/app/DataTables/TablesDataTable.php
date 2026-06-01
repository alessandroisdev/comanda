<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Table;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class TablesDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent com joins necessários.
     */
    public function getQuery(): Builder
    {
        return Table::query()
            ->select([
                'tables.id',
                'tables.uuid',
                'tables.company_id',
                'tables.unit_id',
                'tables.code',
                'tables.name',
                'tables.capacity',
                'tables.sector',
                'tables.status',
                'tables.sort_order',
                'companies.trade_name as company_name',
                'company_units.name as unit_name',
            ])
            ->leftJoin('companies', 'companies.id', '=', 'tables.company_id')
            ->leftJoin('company_units', 'company_units.id', '=', 'tables.unit_id');
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'tables.uuid',
            'code' => 'tables.code',
            'name' => 'tables.name',
            'capacity' => 'tables.capacity',
            'sector' => 'tables.sector',
            'status' => 'tables.status',
            'sort_order' => 'tables.sort_order',
            'company_name' => 'companies.trade_name',
            'unit_name' => 'company_units.name',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'tables.code',
            'tables.name',
            'tables.sector',
            'companies.trade_name',
            'company_units.name',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var Table $row */
        return [
            'uuid' => $row->uuid,
            'code' => $row->code,
            'name' => $row->name,
            'capacity' => $row->capacity,
            'sector' => $row->sector,
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'sort_order' => $row->sort_order,
            'company_name' => $row->getAttribute('company_name') ?? '-',
            'unit_name' => $row->getAttribute('unit_name') ?? '-',
            'actions' => $this->renderActions($row),
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(Table $table): string
    {
        $viewBtn = '';
        $editBtn = '';
        $deleteBtn = '';
        $statusBtn = '';

        if (Gate::allows('view', $table)) {
            $viewBtn = '<a href="/admin/tables/'.$table->uuid.'" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        }

        if (Gate::allows('update', $table)) {
            $editBtn = '<a href="/admin/tables/'.$table->uuid.'/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';
            $statusBtn = '<button type="button" class="btn btn-sm btn-warning btn-change-status me-1" data-uuid="'.$table->uuid.'" data-status="'.$table->status->value.'" title="Alterar Status"><i class="bi bi-gear"></i></button>';
        }

        if (Gate::allows('delete', $table)) {
            $deleteUrl = '/api/v1/tables/'.$table->uuid;
            $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="'.$table->uuid.'" data-url="'.$deleteUrl.'" title="Excluir"><i class="bi bi-trash"></i></button>';
        }

        return '<div class="btn-group">'.$viewBtn.$editBtn.$statusBtn.$deleteBtn.'</div>';
    }
}
