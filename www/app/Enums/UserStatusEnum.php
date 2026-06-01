<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Retorna a descrição localizada do status do usuário.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('users.status.active'),
            self::INACTIVE => __('users.status.inactive'),
        };
    }
}
