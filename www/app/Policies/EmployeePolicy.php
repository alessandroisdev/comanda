<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Verifica se o ator possui uma permissão de forma geral.
     */
    private function hasPermission(mixed $user, string $permission): bool
    {
        // Administradores Gerais do painel (tabela 'users') têm acesso irrestrito
        if ($user instanceof User) {
            return true;
        }

        // Funcionários da tabela 'employees'
        if ($user instanceof Employee) {
            return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })->exists();
        }

        return false;
    }

    /**
     * Determina se o ator pode listar os funcionários.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'employees.view');
    }

    /**
     * Determina se o ator pode visualizar um funcionário específico.
     */
    public function view(mixed $user, Employee $employee): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $employee->company_id && $this->hasPermission($user, 'employees.view');
        }

        return false;
    }

    /**
     * Determina se o ator pode criar funcionários.
     */
    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'employees.create');
    }

    /**
     * Determina se o ator pode atualizar um funcionário.
     */
    public function update(mixed $user, Employee $employee): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $employee->company_id && $this->hasPermission($user, 'employees.update');
        }

        return false;
    }

    /**
     * Determina se o ator pode deletar um funcionário.
     */
    public function delete(mixed $user, Employee $employee): bool
    {
        if ($user instanceof User) {
            // Um admin não pode deletar a si mesmo (se ele estivesse na mesma tabela, mas como está em 'users', ele pode deletar qualquer employee)
            return true;
        }

        if ($user instanceof Employee) {
            // Impedir que o funcionário se autoexclua
            if ($user->id === $employee->id) {
                return false;
            }

            return $user->company_id === $employee->company_id && $this->hasPermission($user, 'employees.delete');
        }

        return false;
    }
}
