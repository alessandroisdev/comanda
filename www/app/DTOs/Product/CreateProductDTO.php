<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use App\Enums\ProductStatusEnum;

class CreateProductDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly int $category_id,
        public readonly string $name,
        public readonly int $price_cents,
        public readonly ?string $sku = null,
        public readonly ?string $barcode = null,
        public readonly ?string $description = null,
        public readonly ?int $cost_cents = null,
        public readonly ProductStatusEnum $status = ProductStatusEnum::ACTIVE,
        public readonly ?string $image = null,
        public readonly int $preparation_time = 0
    ) {}

    /**
     * Cria o DTO a partir de um array sanitizado ou request data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            company_id: (int) $data['company_id'],
            category_id: (int) $data['category_id'],
            sku: ! empty($data['sku']) ? trim($data['sku']) : null,
            barcode: ! empty($data['barcode']) ? trim($data['barcode']) : null,
            name: trim($data['name']),
            description: isset($data['description']) ? trim($data['description']) : null,
            // Certificar que recebemos inteiros nos centavos
            price_cents: (int) $data['price_cents'],
            cost_cents: isset($data['cost_cents']) ? (int) $data['cost_cents'] : null,
            status: isset($data['status'])
                ? (is_string($data['status']) ? ProductStatusEnum::from($data['status']) : $data['status'])
                : ProductStatusEnum::ACTIVE,
            image: ! empty($data['image']) ? trim($data['image']) : null,
            preparation_time: (int) ($data['preparation_time'] ?? 0)
        );
    }
}
