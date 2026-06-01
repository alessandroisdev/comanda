<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    /**
     * Verifica se o ator possui uma permissão de forma geral.
     */
    private function hasPermission(mixed $user, string $permission): bool
    {
        // Se for um Administrador Geral da tabela 'users', ele tem acesso total
        if ($user instanceof \App\Models\User) {
            return true;
        }

        // Se for um funcionário da tabela 'employees'
        if ($user instanceof \App\Models\Employee) {
            // Verificar no relacionamento do RBAC se alguma de suas roles possui a permissão
            return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })->exists();
        }

        return false;
    }

    /**
     * Determina se o ator pode listar as empresas.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'companies.view');
    }

    /**
     * Determina se o ator pode visualizar uma empresa específica.
     */
    public function view(mixed $user, Company $company): bool
    {
        // Administradores globais visualizam tudo
        if ($user instanceof \App\Models\User) {
            return true;
        }

        // Funcionários só visualizam a sua própria empresa
        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $company->id && $this->hasPermission($user, 'companies.view');
        }

        return false;
    }

    /**
     * Determina se o ator pode criar empresas.
     */
    public function create(mixed $user): bool
    {
        // Apenas Administradores do Painel Geral (users) podem criar novas empresas/tenants no sistema
        return $user instanceof \App\Models\User || $this->hasPermission($user, 'companies.create');
    }

    /**
     * Determina se o ator pode atualizar uma empresa.
     */
    public function update(mixed $user, Company $company): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $company->id && $this->hasPermission($user, 'companies.update');
        }

        return false;
    }

    /**
     * Determina se o ator pode deletar uma empresa.
     */
    public function delete(mixed $user, Company $company): bool
    {
        if ($user instanceof \App\Models\User) {
            return true;
        }

        if ($user instanceof \App\Models\Employee) {
            return $user->company_id === $company->id && $this->hasPermission($user, 'companies.delete');
        }

        return false;
    }
}
