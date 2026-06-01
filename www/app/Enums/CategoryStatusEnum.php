<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Retorna a descrição localizada do status da categoria.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('categories.status.active'),
            self::INACTIVE => __('categories.status.inactive'),
        };
    }
}
