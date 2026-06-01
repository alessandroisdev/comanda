<?php

declare(strict_types=1);

namespace App\Enums;

enum EmployeeStatusEnum: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    /**
     * Retorna a descrição localizada do status do funcionário.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('employees.status.active'),
            self::SUSPENDED => __('employees.status.suspended'),
        };
    }
}
