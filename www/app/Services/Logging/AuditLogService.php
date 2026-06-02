<?php

declare(strict_types=1);

namespace App\Services\Logging;

class AuditLogService extends BaseJsonLogService
{
    protected function getLogFilename(): string
    {
        return 'audit.json.log';
    }

    public function log(string $action, string $message, array $context = []): void
    {
        $this->write('NOTICE', $action, $message, $context);
    }
}
