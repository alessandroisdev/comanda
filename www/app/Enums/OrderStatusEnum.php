<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case SENT_TO_KITCHEN = 'sent_to_kitchen';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
