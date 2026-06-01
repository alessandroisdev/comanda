<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Employee;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class EmployeesDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent com joins para empresas e unidades.
     */
    public function getQuery(): Builder
    {
        return Employee::query()
            ->select([
                'employees.id',
                'employees.uuid',
                'employees.company_id',
                'employees.unit_id',
                'employees.employee_number',
                'employees.name',
                'employees.email',
                'employees.phone',
                'employees.role',
                'employees.status',
                'employees.created_at',
                'companies.trade_name as company_name',
                'company_units.name as unit_name',
            ])
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->leftJoin('company_units', 'company_units.id', '=', 'employees.unit_id');
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'employees.uuid',
            'employee_number' => 'employees.employee_number',
            'name' => 'employees.name',
            'email' => 'employees.email',
            'role' => 'employees.role',
            'status' => 'employees.status',
            'company_name' => 'companies.trade_name',
            'unit_name' => 'company_units.name',
            'created_at' => 'employees.created_at',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'employees.name',
            'employees.email',
            'employees.employee_number',
            'employees.phone',
            'companies.trade_name',
            'company_units.name',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var Employee $row */
        return [
            'uuid' => $row->uuid,
            'employee_number' => $row->employee_number,
            'name' => $row->name,
            'email' => $row->email,
            'phone' => $row->phone,
            'role' => $row->role->value,
            'role_label' => $row->role->label(),
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'company_name' => $row->getAttribute('company_name') ?? '-',
            'unit_name' => $row->getAttribute('unit_name') ?? '-',
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row),
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(Employee $employee): string
    {
        $viewBtn = '<a href="/admin/employees/'.$employee->uuid.'" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/employees/'.$employee->uuid.'/edit" class="btn btn-sm btn-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>';

        $deleteUrl = '/api/v1/employees/'.$employee->uuid;
        $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="'.$employee->uuid.'" data-url="'.$deleteUrl.'" title="Excluir"><i class="bi bi-trash"></i></button>';

        return '<div class="btn-group">'.$viewBtn.$editBtn.$deleteBtn.'</div>';
    }
}
