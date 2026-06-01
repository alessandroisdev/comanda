<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Retorna a descrição localizada do status do produto.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('products.status.active'),
            self::INACTIVE => __('products.status.inactive'),
        };
    }
}
