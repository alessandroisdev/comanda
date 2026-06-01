<?php

declare(strict_types=1);

namespace App\Enums;

enum KitchenTicketStatusEnum: string
{
    case PENDING = 'pending';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
