<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Verifica se o ator possui uma permissão de forma geral.
     */
    private function hasPermission(mixed $user, string $permission): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })->exists();
        }

        return false;
    }

    /**
     * Determina se o ator pode listar as categorias.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'categories.view');
    }

    /**
     * Determina se o ator pode visualizar uma categoria específica.
     */
    public function view(mixed $user, Category $category): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $category->company_id && $this->hasPermission($user, 'categories.view');
        }

        return false;
    }

    /**
     * Determina se o ator pode criar categorias.
     */
    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'categories.create');
    }

    /**
     * Determina se o ator pode atualizar uma categoria.
     */
    public function update(mixed $user, Category $category): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $category->company_id && $this->hasPermission($user, 'categories.update');
        }

        return false;
    }

    /**
     * Determina se o ator pode deletar uma categoria.
     */
    public function delete(mixed $user, Category $category): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $category->company_id && $this->hasPermission($user, 'categories.delete');
        }

        return false;
    }
}
