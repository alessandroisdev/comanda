<?php

declare(strict_types=1);

namespace App\Enums;

enum TableStatusEnum: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
    case BLOCKED = 'blocked';
    case CLEANING = 'cleaning';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponível',
            self::OCCUPIED => 'Ocupada',
            self::RESERVED => 'Reservada',
            self::BLOCKED => 'Bloqueada',
            self::CLEANING => 'Limpeza',
        };
    }
}
