<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
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
     * Determina se o ator pode listar os clientes.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'customers.view');
    }

    /**
     * Determina se o ator pode visualizar um cliente específico.
     */
    public function view(mixed $user, Customer $customer): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $customer->company_id && $this->hasPermission($user, 'customers.view');
        }

        return false;
    }

    /**
     * Determina se o ator pode criar clientes.
     */
    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'customers.create');
    }

    /**
     * Determina se o ator pode atualizar um cliente.
     */
    public function update(mixed $user, Customer $customer): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $customer->company_id && $this->hasPermission($user, 'customers.update');
        }

        return false;
    }

    /**
     * Determina se o ator pode deletar um cliente.
     */
    public function delete(mixed $user, Customer $customer): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $customer->company_id && $this->hasPermission($user, 'customers.delete');
        }

        return false;
    }
}
