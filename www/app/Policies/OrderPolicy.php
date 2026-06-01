<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
        return $this->hasPermission($user, 'orders.view');
    }

    public function view(mixed $user, Order $order): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $order->company_id && $this->hasPermission($user, 'orders.view');
        }

        return false;
    }

    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'orders.create');
    }

    public function update(mixed $user, Order $order): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $order->company_id && $this->hasPermission($user, 'orders.update');
        }

        return false;
    }

    public function delete(mixed $user, Order $order): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $order->company_id && $this->hasPermission($user, 'orders.update');
        }

        return false;
    }
}
