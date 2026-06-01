<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderSessionStatusEnum: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Aberta',
            self::CLOSED => 'Fechada',
            self::CANCELLED => 'Cancelada',
        };
    }
}
