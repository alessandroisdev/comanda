<?php

declare(strict_types=1);

namespace App\Logging;

use App\Services\Logging\LogSanitizerProcessor;
use Monolog\Logger;

class LogProcessorTapper
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(mixed $logger): void
    {
        @file_put_contents(storage_path('logs/tapper_debug.log'), 'Tapper called with logger class: '.(is_object($logger) ? get_class($logger) : gettype($logger))."\n", FILE_APPEND);

        $monolog = null;
        if (method_exists($logger, 'getLogger')) {
            $monolog = $logger->getLogger();
        } elseif (method_exists($logger, 'getMonolog')) {
            $monolog = $logger->getMonolog();
        } elseif ($logger instanceof Logger) {
            $monolog = $logger;
        }

        if ($monolog && method_exists($monolog, 'pushProcessor')) {
            $monolog->pushProcessor(new LogSanitizerProcessor);
            @file_put_contents(storage_path('logs/tapper_debug.log'), "Successfully pushed processor to Monolog\n", FILE_APPEND);
        } else {
            @file_put_contents(storage_path('logs/tapper_debug.log'), "Failed to resolve Monolog or pushProcessor\n", FILE_APPEND);
        }
    }
}
