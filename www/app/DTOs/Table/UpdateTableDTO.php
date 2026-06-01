<?php

declare(strict_types=1);

namespace App\DTOs\Table;

class UpdateTableDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $capacity,
        public readonly string $sector,
        public readonly int $sort_order = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) $data['code'],
            name: (string) $data['name'],
            capacity: (int) $data['capacity'],
            sector: (string) $data['sector'],
            sort_order: (int) ($data['sort_order'] ?? 0)
        );
    }
}
