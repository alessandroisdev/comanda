<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Verifica se o ator possui uma permissão de forma geral.
     */
    private function hasPermission(mixed $actor, string $permission): bool
    {
        if ($actor instanceof User) {
            return true; // Administradores do Painel Geral têm controle irrestrito
        }

        if ($actor instanceof Employee) {
            return $actor->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })->exists();
        }

        return false;
    }

    /**
     * Determina se o ator pode listar usuários.
     */
    public function viewAny(mixed $actor): bool
    {
        return $this->hasPermission($actor, 'users.view');
    }

    /**
     * Determina se o ator pode visualizar um usuário específico.
     */
    public function view(mixed $actor, User $targetUser): bool
    {
        if ($actor instanceof User) {
            return true;
        }

        return $this->hasPermission($actor, 'users.view');
    }

    /**
     * Determina se o ator pode criar usuários.
     */
    public function create(mixed $actor): bool
    {
        return $actor instanceof User || $this->hasPermission($actor, 'users.create');
    }

    /**
     * Determina se o ator pode atualizar um usuário.
     */
    public function update(mixed $actor, User $targetUser): bool
    {
        if ($actor instanceof User) {
            return true;
        }

        return $this->hasPermission($actor, 'users.update');
    }

    /**
     * Determina se o ator pode deletar um usuário.
     */
    public function delete(mixed $actor, User $targetUser): bool
    {
        if ($actor instanceof User) {
            // Impedir que o usuário delete a si mesmo
            return $actor->id !== $targetUser->id;
        }

        return $this->hasPermission($actor, 'users.delete');
    }
}
