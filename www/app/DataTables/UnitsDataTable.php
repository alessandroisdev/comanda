<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\CompanyUnit;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class UnitsDataTable implements DataTableProviderInterface
{
    private ?int $companyId;

    public function __construct(?int $companyId = null)
    {
        $this->companyId = $companyId;
    }

    /**
     * Retorna a query base do Eloquent de Unidades, aplicando escopo opcional de Empresa.
     */
    public function getQuery(): Builder
    {
        $query = CompanyUnit::query()->select([
            'company_units.id',
            'company_units.uuid',
            'company_units.company_id',
            'company_units.status',
            'company_units.name',
            'company_units.document_number',
            'company_units.email',
            'company_units.phone',
            'company_units.city',
            'company_units.state',
            'company_units.created_at',
        ])->with('company');

        if ($this->companyId) {
            $query->where('company_units.company_id', $this->companyId);
        }

        return $query;
    }

    /**
     * Colunas da whitelist permitidas para ordenação.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'company_units.uuid',
            'status' => 'company_units.status',
            'name' => 'company_units.name',
            'document_number' => 'company_units.document_number',
            'city' => 'company_units.city',
            'created_at' => 'company_units.created_at',
        ];
    }

    /**
     * Colunas da busca global textual.
     */
    public function getSearchableColumns(): array
    {
        return [
            'company_units.name',
            'company_units.document_number',
            'company_units.city',
            'company_units.state',
            'company_units.email',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var CompanyUnit $row */
        return [
            'uuid' => $row->uuid,
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'name' => $row->name,
            'company_name' => $row->company->trade_name,
            'document_number' => $row->document_number ?? '-',
            'email' => $row->email ?? '-',
            'phone' => $row->phone ?? '-',
            'location' => $row->city . ' / ' . $row->state,
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row)
        ];
    }

    /**
     * Renderiza botões HTML customizados protegidos por Policy.
     */
    private function renderActions(CompanyUnit $unit): string
    {
        $viewBtn = '<a href="/admin/units/' . $unit->uuid . '" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/units/' . $unit->uuid . '/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';
        
        $deleteUrl = '/api/v1/units/' . $unit->uuid;
        $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="' . $unit->uuid . '" data-url="' . $deleteUrl . '" title="Excluir"><i class="bi bi-trash"></i></button>';

        return '<div class="btn-group">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
    }
}
