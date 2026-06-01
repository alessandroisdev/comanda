<?php

declare(strict_types=1);

namespace App\Enums;

enum EmployeeRoleEnum: string
{
    case WAITER = 'waiter';
    case CASHIER = 'cashier';
    case KITCHEN = 'kitchen';
    case MANAGER = 'manager';
    case ADMIN = 'admin';
    case DRIVER = 'driver';

    /**
     * Retorna a descrição amigável do cargo.
     */
    public function label(): string
    {
        return match ($this) {
            self::WAITER => __('employees.roles.waiter'),
            self::CASHIER => __('employees.roles.cashier'),
            self::KITCHEN => __('employees.roles.kitchen'),
            self::MANAGER => __('employees.roles.manager'),
            self::ADMIN => __('employees.roles.admin'),
            self::DRIVER => __('employees.roles.driver'),
        };
    }
}
