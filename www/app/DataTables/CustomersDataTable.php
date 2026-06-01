<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Customer;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class CustomersDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent com join com a empresa.
     */
    public function getQuery(): Builder
    {
        return Customer::query()
            ->select([
                'customers.id',
                'customers.uuid',
                'customers.company_id',
                'customers.name',
                'customers.email',
                'customers.phone',
                'customers.document',
                'customers.status',
                'customers.created_at',
                'companies.trade_name as company_name',
            ])
            ->leftJoin('companies', 'companies.id', '=', 'customers.company_id');
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'customers.uuid',
            'name' => 'customers.name',
            'email' => 'customers.email',
            'status' => 'customers.status',
            'company_name' => 'companies.trade_name',
            'created_at' => 'customers.created_at',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'customers.name',
            'customers.email',
            'customers.phone',
            'customers.document',
            'companies.trade_name',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var Customer $row */
        return [
            'uuid' => $row->uuid,
            'name' => $row->name,
            'email' => $row->email,
            'phone' => $row->phone,
            'document' => $row->document,
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'company_name' => $row->getAttribute('company_name') ?? '-',
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row),
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(Customer $customer): string
    {
        $viewBtn = '<a href="/admin/customers/'.$customer->uuid.'" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/customers/'.$customer->uuid.'/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';

        $deleteUrl = '/api/v1/customers/'.$customer->uuid;
        $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="'.$customer->uuid.'" data-url="'.$deleteUrl.'" title="Excluir"><i class="bi bi-trash"></i></button>';

        return '<div class="btn-group">'.$viewBtn.$editBtn.$deleteBtn.'</div>';
    }
}
