<?php

declare(strict_types=1);

namespace App\DTOs\Category;

use App\Enums\CategoryStatusEnum;

class UpdateCategoryDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly CategoryStatusEnum $status = CategoryStatusEnum::ACTIVE,
        public readonly int $sort_order = 0
    ) {}

    /**
     * Cria o DTO a partir de um array sanitizado ou request data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            description: isset($data['description']) ? trim($data['description']) : null,
            status: isset($data['status'])
                ? (is_string($data['status']) ? CategoryStatusEnum::from($data['status']) : $data['status'])
                : CategoryStatusEnum::ACTIVE,
            sort_order: (int) ($data['sort_order'] ?? 0)
        );
    }
}
