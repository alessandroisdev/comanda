<?php

declare(strict_types=1);

namespace App\Services\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogSanitizerProcessor implements ProcessorInterface
{
    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        if ($record instanceof LogRecord) {
            // Suporte para Monolog v3
            $message = LogSanitizer::sanitizeText($record->message);
            $context = LogSanitizer::sanitize($record->context);
            $extra = LogSanitizer::sanitize($record->extra);

            return $record->with(
                message: $message,
                context: $context,
                extra: $extra
            );
        }

        // Suporte para Monolog v2
        if (isset($record['message'])) {
            $record['message'] = LogSanitizer::sanitizeText((string) $record['message']);
        }
        if (isset($record['context'])) {
            $record['context'] = LogSanitizer::sanitize($record['context']);
        }
        if (isset($record['extra'])) {
            $record['extra'] = LogSanitizer::sanitize($record['extra']);
        }

        return $record;
    }
}
