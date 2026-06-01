<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanyStatusEnum: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    /**
     * Retorna a descrição internacionalizada ou traduzida do status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('companies.status.active'),
            self::SUSPENDED => __('companies.status.suspended'),
        };
    }
}
