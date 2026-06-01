<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Product;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class ProductsDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent com join com a empresa e categoria.
     */
    public function getQuery(): Builder
    {
        return Product::query()
            ->select([
                'products.id',
                'products.uuid',
                'products.company_id',
                'products.category_id',
                'products.sku',
                'products.barcode',
                'products.name',
                'products.description',
                'products.price_cents',
                'products.cost_cents',
                'products.status',
                'products.preparation_time',
                'products.created_at',
                'companies.trade_name as company_name',
                'categories.name as category_name',
            ])
            ->leftJoin('companies', 'companies.id', '=', 'products.company_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id');
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'products.uuid',
            'sku' => 'products.sku',
            'name' => 'products.name',
            'price_cents' => 'products.price_cents',
            'status' => 'products.status',
            'company_name' => 'companies.trade_name',
            'category_name' => 'categories.name',
            'created_at' => 'products.created_at',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'products.name',
            'products.sku',
            'products.barcode',
            'products.description',
            'companies.trade_name',
            'categories.name',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var Product $row */
        return [
            'uuid' => $row->uuid,
            'sku' => $row->sku ?? '-',
            'barcode' => $row->barcode ?? '-',
            'name' => $row->name,
            'description' => $row->description,
            'price_cents' => $row->price_cents,
            'price_formatted' => $row->formatted_price,
            'cost_cents' => $row->cost_cents,
            'cost_formatted' => $row->formatted_cost ?? '-',
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'preparation_time' => $row->preparation_time,
            'company_name' => $row->getAttribute('company_name') ?? '-',
            'category_name' => $row->getAttribute('category_name') ?? '-',
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row),
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(Product $product): string
    {
        $viewBtn = '<a href="/admin/products/'.$product->uuid.'" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/products/'.$product->uuid.'/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';

        $deleteUrl = '/api/v1/products/'.$product->uuid;
        $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="'.$product->uuid.'" data-url="'.$deleteUrl.'" title="Excluir"><i class="bi bi-trash"></i></button>';

        return '<div class="btn-group">'.$viewBtn.$editBtn.$deleteBtn.'</div>';
    }
}
