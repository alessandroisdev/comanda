<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\OrderSession;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class OrderSessionsDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent com joins necessários.
     */
    public function getQuery(): Builder
    {
        return OrderSession::query()
            ->select([
                'orders_sessions.id',
                'orders_sessions.uuid',
                'orders_sessions.company_id',
                'orders_sessions.unit_id',
                'orders_sessions.table_id',
                'orders_sessions.status',
                'orders_sessions.opened_at',
                'orders_sessions.closed_at',
                'orders_sessions.people_count',
                'tables.name as table_name',
                'tables.code as table_code',
                'employees.name as opened_by_name',
            ])
            ->leftJoin('tables', 'tables.id', '=', 'orders_sessions.table_id')
            ->leftJoin('employees', 'employees.id', '=', 'orders_sessions.opened_by_employee_id');
    }

    /**
     * Colunas permitidas para ordenação no banco.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'orders_sessions.uuid',
            'table_name' => 'tables.name',
            'status' => 'orders_sessions.status',
            'people_count' => 'orders_sessions.people_count',
            'opened_at' => 'orders_sessions.opened_at',
            'closed_at' => 'orders_sessions.closed_at',
        ];
    }

    /**
     * Colunas permitidas na busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'tables.name',
            'tables.code',
            'employees.name',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var OrderSession $row */
        return [
            'uuid' => $row->uuid,
            'table_name' => $row->getAttribute('table_name') ?? 'Individual',
            'table_code' => $row->getAttribute('table_code') ?? '-',
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'people_count' => $row->people_count,
            'opened_by_name' => $row->getAttribute('opened_by_name') ?? '-',
            'opened_at' => $row->opened_at->toIso8601String(),
            'closed_at' => $row->closed_at ? $row->closed_at->toIso8601String() : '-',
            'actions' => $this->renderActions($row),
        ];
    }

    /**
     * Renderiza botões HTML customizados e protegidos por Policy.
     */
    private function renderActions(OrderSession $session): string
    {
        $viewBtn = '';
        $closeBtn = '';
        $cancelBtn = '';
        $transferBtn = '';

        if (Gate::allows('view', $session)) {
            $viewBtn = '<a href="/admin/sessions/'.$session->uuid.'" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        }

        if ($session->status->value === 'open') {
            if (Gate::allows('update', $session)) {
                $closeBtn = '<button type="button" class="btn btn-sm btn-success btn-close-session me-1" data-uuid="'.$session->uuid.'" title="Fechar Comanda"><i class="bi bi-check-circle"></i></button>';
                $cancelBtn = '<button type="button" class="btn btn-sm btn-danger btn-cancel-session me-1" data-uuid="'.$session->uuid.'" title="Cancelar Comanda"><i class="bi bi-x-circle"></i></button>';
                $transferBtn = '<button type="button" class="btn btn-sm btn-warning btn-transfer-session me-1" data-uuid="'.$session->uuid.'" title="Transferir Mesa"><i class="bi bi-arrow-left-right"></i></button>';
            }
        }

        return '<div class="btn-group">'.$viewBtn.$closeBtn.$transferBtn.$cancelBtn.'</div>';
    }
}
