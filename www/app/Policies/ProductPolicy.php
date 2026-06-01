<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
     * Determina se o ator pode listar os produtos.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'products.view');
    }

    /**
     * Determina se o ator pode visualizar um produto específico.
     */
    public function view(mixed $user, Product $product): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $product->company_id && $this->hasPermission($user, 'products.view');
        }

        return false;
    }

    /**
     * Determina se o ator pode criar produtos.
     */
    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'products.create');
    }

    /**
     * Determina se o ator pode atualizar um produto.
     */
    public function update(mixed $user, Product $product): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $product->company_id && $this->hasPermission($user, 'products.update');
        }

        return false;
    }

    /**
     * Determina se o ator pode deletar um produto.
     */
    public function delete(mixed $user, Product $product): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $product->company_id && $this->hasPermission($user, 'products.delete');
        }

        return false;
    }
}
