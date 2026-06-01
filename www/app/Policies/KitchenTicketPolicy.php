<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\KitchenTicket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KitchenTicketPolicy
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
        return $this->hasPermission($user, 'kitchen.view');
    }

    public function view(mixed $user, KitchenTicket $ticket): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $ticket->order->company_id && $this->hasPermission($user, 'kitchen.view');
        }

        return false;
    }

    public function create(mixed $user): bool
    {
        return $this->hasPermission($user, 'kitchen.update');
    }

    public function update(mixed $user, KitchenTicket $ticket): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $ticket->order->company_id && $this->hasPermission($user, 'kitchen.update');
        }

        return false;
    }

    public function delete(mixed $user, KitchenTicket $ticket): bool
    {
        if ($user instanceof User) {
            return true;
        }

        if ($user instanceof Employee) {
            return $user->company_id === $ticket->order->company_id && $this->hasPermission($user, 'kitchen.update');
        }

        return false;
    }
}
