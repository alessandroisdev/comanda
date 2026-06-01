<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CompanyUnit;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyUnitPolicy
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
     * Determina se o ator pode listar as unidades.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'units.view');
    }

    /**
     * Determina se o ator pode visualizar uma unidade específica.
     */
    public function view(mixed $user, CompanyUnit $unit): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $unit->company_id && $this->hasPermission($user, 'units.view');
        }

        return false;
    }

    /**
     * Determina se o ator pode criar unidades.
     */
    public function create(mixed $user): bool
    {
        return $user instanceof \App\Models\User || $this->hasPermission($user, 'units.create');
    }

    /**
     * Determina se o ator pode atualizar uma unidade.
     */
    public function update(mixed $user, CompanyUnit $unit): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $unit->company_id && $this->hasPermission($user, 'units.update');
        }

        return false;
    }

    /**
     * Determina se o ator pode deletar uma unidade.
     */
    public function delete(mixed $user, CompanyUnit $unit): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $unit->company_id && $this->hasPermission($user, 'units.delete');
        }

        return false;
    }
}
