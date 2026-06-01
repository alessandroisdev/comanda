<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Retorna o rótulo traduzido do status da unidade.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('units.status.active'),
            self::INACTIVE => __('units.status.inactive'),
        };
    }
}
