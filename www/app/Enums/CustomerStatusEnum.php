<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Retorna a descrição localizada do status do cliente.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('customers.status.active'),
            self::INACTIVE => __('customers.status.inactive'),
        };
    }
}
