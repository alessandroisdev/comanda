<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\User;
use App\Services\DataTables\Contracts\DataTableProviderInterface;
use Illuminate\Database\Eloquent\Builder;

class UsersDataTable implements DataTableProviderInterface
{
    /**
     * Retorna a query base do Eloquent de Usuários.
     */
    public function getQuery(): Builder
    {
        return User::query()->select([
            'id',
            'uuid',
            'status',
            'name',
            'email',
            'created_at',
        ]);
    }

    /**
     * Colunas da whitelist permitidas para ordenação.
     */
    public function getSortableColumnsWhitelist(): array
    {
        return [
            'uuid' => 'users.uuid',
            'status' => 'users.status',
            'name' => 'users.name',
            'email' => 'users.email',
            'created_at' => 'users.created_at',
        ];
    }

    /**
     * Colunas da busca global.
     */
    public function getSearchableColumns(): array
    {
        return [
            'users.name',
            'users.email',
        ];
    }

    /**
     * Formata os campos da linha antes de enviar ao frontend.
     */
    public function formatRow(mixed $row): array
    {
        /** @var User $row */
        return [
            'uuid' => $row->uuid,
            'status' => $row->status->value,
            'status_label' => $row->status->label(),
            'name' => $row->name,
            'email' => $row->email,
            'created_at' => $row->created_at->toIso8601String(),
            'actions' => $this->renderActions($row)
        ];
    }

    /**
     * Renderiza botões HTML customizados.
     */
    private function renderActions(User $user): string
    {
        $viewBtn = '<a href="/admin/users/' . $user->uuid . '" class="btn btn-sm btn-info me-1" title="Visualizar"><i class="bi bi-eye"></i></a>';
        $editBtn = '<a href="/admin/users/' . $user->uuid . '/edit" class="btn btn-sm btn-primary me-1" title="Editar / Reset de Senha"><i class="bi bi-pencil"></i></a>';
        
        $deleteUrl = '/api/v1/users/' . $user->uuid;
        
        // Evitar que o usuário logado veja o botão de apagar a si mesmo no datatable
        $currentUser = auth()->user();
        $deleteBtn = '';
        if ($currentUser && $currentUser->getKey() !== $user->id) {
            $deleteBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete-row" data-uuid="' . $user->uuid . '" data-url="' . $deleteUrl . '" title="Excluir"><i class="bi bi-trash"></i></button>';
        }

        return '<div class="btn-group">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
    }
}
