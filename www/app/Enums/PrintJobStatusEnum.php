<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintJobStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PRINTED = 'printed';
    case FAILED = 'failed';
}
