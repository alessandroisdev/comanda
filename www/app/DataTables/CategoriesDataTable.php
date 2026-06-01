<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Category;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class CategoriesDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent com join com a empresa.
     */
    public function getQuery(): Builder
    {
        return Category::query()
            ->select([
                'categories.id',
                'categories.uuid',
                'categories.company_id',
                'categories.name',
                'categories.description',
                'categories.status',
                'categories.sort_order',
                'categories.created_at',
                'companies.trade_name as company_name'
            ])
            ->leftJoin('companies', 'companies.id', '=', 'categories.company_id');
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'categories.uuid',
            'name' => 'categories.name',
            'status' => 'categories.status',
            'sort_order' => 'categories.sort_order',
            'company_name' => 'companies.trade_name',
            'created_at' => 'categories.created_at',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'categories.name',
            'categories.description',
            'companies.trade_name',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var Category $row */
        return [
            'uuid' => $row->uuid,
            'name' => $row->name,
            'description' => $row->description,
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'sort_order' => $row->sort_order,
            'company_name' => $row->getAttribute('company_name') ?? '-',
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row)
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(Category $category): string
    {
        $viewBtn = '<a href="/admin/categories/' . $category->uuid . '" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/categories/' . $category->uuid . '/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';
        
        $deleteUrl = '/api/v1/categories/' . $category->uuid;
        $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="' . $category->uuid . '" data-url="' . $deleteUrl . '" title="Excluir"><i class="bi bi-trash"></i></button>';

        return '<div class="btn-group">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
    }
}
