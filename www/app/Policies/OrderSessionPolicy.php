<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\OrderSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderSessionPolicy
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
        return $this->hasPermission($user, 'sessions.view');
    }

    public function view(mixed $user, OrderSession $session): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $session->company_id && $this->hasPermission($user, 'sessions.view');
        }

        return false;
    }

    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'sessions.open');
    }

    public function update(mixed $user, OrderSession $session): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $session->company_id && $this->hasPermission($user, 'sessions.update');
        }

        return false;
    }

    public function delete(mixed $user, OrderSession $session): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $session->company_id && $this->hasPermission($user, 'sessions.close');
        }

        return false;
    }
}
