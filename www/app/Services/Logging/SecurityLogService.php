<?php

declare(strict_types=1);

namespace App\Services\Logging;

class SecurityLogService extends BaseJsonLogService
{
    protected function getLogFilename(): string
    {
        return 'security.json.log';
    }

    public function alert(string $action, string $message, array $context = []): void
    {
        $this->write('ALERT', $action, $message, $context);
    }

    public function critical(string $action, string $message, array $context = []): void
    {
        $this->write('CRITICAL', $action, $message, $context);
    }

    public function warning(string $action, string $message, array $context = []): void
    {
        $this->write('WARNING', $action, $message, $context);
    }
}
