<?php

declare(strict_types=1);

namespace App\Services\Logging;

class BusinessLogService extends BaseJsonLogService
{
    protected function getLogFilename(): string
    {
        return 'business.json.log';
    }

    public function event(string $action, string $message, array $context = []): void
    {
        $this->write('INFO', $action, $message, $context);
    }
}
