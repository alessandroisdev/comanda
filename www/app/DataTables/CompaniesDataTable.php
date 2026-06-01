<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Company;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class CompaniesDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent.
     */
    public function getQuery(): Builder
    {
        return Company::query()->select([
            'id',
            'uuid',
            'status',
            'legal_name',
            'trade_name',
            'document_type',
            'document_number',
            'email',
            'phone',
            'created_at',
        ]);
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'companies.uuid',
            'status' => 'companies.status',
            'legal_name' => 'companies.legal_name',
            'trade_name' => 'companies.trade_name',
            'document_number' => 'companies.document_number',
            'email' => 'companies.email',
            'created_at' => 'companies.created_at',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'companies.legal_name',
            'companies.trade_name',
            'companies.document_number',
            'companies.email',
            'companies.phone',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var Company $row */
        return [
            'uuid' => $row->uuid,
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'legal_name' => $row->legal_name,
            'trade_name' => $row->trade_name,
            'document_type' => $row->document_type->value,
            'document_number' => $row->document_number,
            'email' => $row->email,
            'phone' => $row->phone,
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row),
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(Company $company): string
    {
        $viewBtn = '<a href="/admin/companies/'.$company->uuid.'" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/companies/'.$company->uuid.'/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';

        $deleteUrl = '/api/v1/companies/'.$company->uuid;
        $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="'.$company->uuid.'" data-url="'.$deleteUrl.'" title="Excluir"><i class="bi bi-trash"></i></button>';

        return '<div class="btn-group">'.$viewBtn.$editBtn.$deleteBtn.'</div>';
    }
}
