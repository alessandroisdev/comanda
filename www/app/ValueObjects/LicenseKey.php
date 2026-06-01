<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class LicenseKey
{
    private string $key;

    public function __construct(string $key)
    {
        $this->validate($key);
        $this->key = trim($key);
    }

    private function validate(string $key): void
    {
        if (empty(trim($key))) {
            throw new InvalidArgumentException(__('licenses.errors.key_empty'));
        }

        // Licença em formato JWT ou criptografada RSA deve ser longa o suficiente
        if (strlen($key) < 64) {
            throw new InvalidArgumentException(__('licenses.errors.key_too_short'));
        }
    }

    public function value(): string
    {
        return $this->key;
    }

    public function equals(LicenseKey $other): bool
    {
        return $this->key === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
