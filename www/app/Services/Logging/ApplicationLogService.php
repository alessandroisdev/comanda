<?php

declare(strict_types=1);

namespace App\Services\Logging;

class ApplicationLogService extends BaseJsonLogService
{
    protected function getLogFilename(): string
    {
        return 'application.json.log';
    }

    public function info(string $action, string $message, array $context = []): void
    {
        $this->write('INFO', $action, $message, $context);
    }

    public function error(string $action, string $message, array $context = []): void
    {
        $this->write('ERROR', $action, $message, $context);
    }

    public function warning(string $action, string $message, array $context = []): void
    {
        $this->write('WARNING', $action, $message, $context);
    }
}
