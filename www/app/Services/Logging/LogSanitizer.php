<?php

declare(strict_types=1);

namespace App\Services\Logging;

class LogSanitizer
{
    private static array $sensitiveKeys = [
        'cpf', 'cnpj', 'email', 'telefone', 'phone', 'document', 'password',
        'activation_key', 'private_key', 'token', 'signature', 'key_data',
    ];

    public static function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (in_array(strtolower((string) $key), self::$sensitiveKeys)) {
                    $data[$key] = self::maskValue((string) $key, $value);
                } else {
                    $data[$key] = self::sanitize($value);
                }
            }

            return $data;
        }

        if ($data instanceof \Throwable) {
            return [
                'class' => get_class($data),
                'message' => self::sanitizeText($data->getMessage()),
                'code' => $data->getCode(),
                'file' => $data->getFile(),
                'line' => $data->getLine(),
                'trace' => self::sanitizeText($data->getTraceAsString()),
            ];
        }

        if (is_object($data)) {
            if (method_exists($data, '__toString')) {
                return self::sanitizeText($data->__toString());
            }
            try {
                return self::sanitize((array) $data);
            } catch (\Throwable $e) {
                return '[OBJECT]';
            }
        }

        if (is_string($data)) {
            return self::sanitizeText($data);
        }

        return $data;
    }

    private static function maskValue(string $key, mixed $value): string
    {
        if (empty($value)) {
            return (string) $value;
        }

        $valStr = (string) $value;

        switch (strtolower($key)) {
            case 'password':
            case 'private_key':
            case 'activation_key':
            case 'key_data':
                return '[REDACTED]';
            case 'token':
            case 'signature':
                return strlen($valStr) > 10 ? substr($valStr, 0, 10).'...[REDACTED]' : '[REDACTED]';
            case 'cpf':
            case 'document':
                if (self::isValidCpf($valStr)) {
                    $clean = preg_replace('/[^0-9]/', '', $valStr);

                    return substr($clean, 0, 3).'.***.***-**';
                }

                return '***.***.***-**';
            case 'cnpj':
                return '**.***.***/****-**';
            case 'email':
                $parts = explode('@', $valStr);
                if (count($parts) === 2) {
                    return substr($parts[0], 0, 2).'***@'.$parts[1];
                }

                return '***@***.***';
            case 'telefone':
            case 'phone':
                return '(**) *****-****';
            default:
                return '[REDACTED]';
        }
    }

    public static function sanitizeText(string $text): string
    {
        // 1. Redact Private Keys block
        $text = preg_replace('/-----BEGIN (RSA )?PRIVATE KEY-----(.*?)-----END (RSA )?PRIVATE KEY-----/s', '[REDACTED PRIVATE KEY]', $text);

        // 2. Redact Emails
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[REDACTED EMAIL]', $text);

        // 3. Redact CPFs with format (ex: 123.456.789-01)
        $text = preg_replace_callback('/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', function ($matches) {
            return '***.***.***-**';
        }, $text);

        // 4. Redact raw 11-digit CPFs (only if they are valid CPFs to avoid breaking UUID segments)
        $text = preg_replace_callback('/\b\d{11}\b/', function ($matches) {
            if (self::isValidCpf($matches[0])) {
                return '***********';
            }

            return $matches[0];
        }, $text);

        // 5. Redact CNPJs (format and raw)
        $text = preg_replace_callback('/\b\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}\b/', function ($matches) {
            return '**.***.***/****-**';
        }, $text);

        $text = preg_replace_callback('/\b\d{14}\b/', function ($matches) {
            if (self::isValidCnpj($matches[0])) {
                return '**************';
            }

            return $matches[0];
        }, $text);

        return $text;
    }

    private static function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11) {
            return false;
        }
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$c] !== $d) {
                return false;
            }
        }

        return true;
    }

    private static function isValidCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14) {
            return false;
        }
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        for ($t = 12; $t < 14; $t++) {
            $d = 0;
            $c = 0;
            $p = $t - 7;
            for ($i = $p; $i >= 2; $i--) {
                $d += (int) $cnpj[$c++] * $i;
            }
            for ($i = 9; $i >= 2; $i--) {
                if ($c < $t) {
                    $d += (int) $cnpj[$c++] * $i;
                }
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cnpj[$c] !== $d) {
                return false;
            }
        }

        return true;
    }
}
