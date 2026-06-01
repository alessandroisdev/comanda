<?php

declare(strict_types=1);

namespace App\DTOs\Table;

class CreateTableDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly int $unit_id,
        public readonly string $code,
        public readonly string $name,
        public readonly int $capacity,
        public readonly string $sector,
        public readonly string $status = 'available',
        public readonly int $sort_order = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            company_id: (int) $data['company_id'],
            unit_id: (int) $data['unit_id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            capacity: (int) ($data['capacity'] ?? 4),
            sector: (string) ($data['sector'] ?? 'Salão'),
            status: (string) ($data['status'] ?? 'available'),
            sort_order: (int) ($data['sort_order'] ?? 0)
        );
    }
}
