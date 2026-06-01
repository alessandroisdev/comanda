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
}
