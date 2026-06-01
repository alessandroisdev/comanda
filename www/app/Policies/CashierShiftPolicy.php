<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CashierShift;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashierShiftPolicy
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
        return $this->hasPermission($user, 'cashier.view');
    }

    public function view(mixed $user, CashierShift $shift): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $shift->company_id && $this->hasPermission($user, 'cashier.view');
        }

        return false;
    }

    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'cashier.open');
    }

    public function update(mixed $user, CashierShift $shift): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $shift->company_id && $this->hasPermission($user, 'cashier.close');
        }

        return false;
    }
}
