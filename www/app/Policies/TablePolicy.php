<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TablePolicy
{
    use HandlesAuthorization;

    private function hasPermission(mixed $user, string $permission): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })->exists();
        }

        return false;
    }

    public function viewAny(mixed $user): bool
    {
        return $this->hasPermission($user, 'tables.view');
    }

    public function view(mixed $user, Table $table): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $table->company_id && $this->hasPermission($user, 'tables.view');
        }

        return false;
    }

    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'tables.create');
    }

    public function update(mixed $user, Table $table): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $table->company_id && $this->hasPermission($user, 'tables.update');
        }

        return false;
    }

    public function delete(mixed $user, Table $table): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $table->company_id && $this->hasPermission($user, 'tables.delete');
        }

        return false;
    }
}
