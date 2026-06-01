<?php

declare(strict_types=1);

namespace App\DTOs\Unit;

use App\Enums\UnitStatusEnum;

class UpdateUnitDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $zipcode,
        public readonly string $street,
        public readonly string $number,
        public readonly string $district,
        public readonly string $city,
        public readonly string $state,
        public readonly string $country,
        public readonly ?string $document_number = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?array $settings_json = null,
        public readonly ?UnitStatusEnum $status = null
    ) {}

    /**
     * Instancia o DTO a partir de um array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            zipcode: preg_replace('/[^0-9]/', '', $data['zipcode']),
            street: $data['street'],
            number: $data['number'],
            district: $data['district'],
            city: $data['city'],
            state: $data['state'],
            country: $data['country'] ?? 'Brasil',
            document_number: ! empty($data['document_number'])
                ? preg_replace('/[^0-9]/', '', $data['document_number'])
                : null,
            email: ! empty($data['email']) ? strtolower(trim($data['email'])) : null,
            phone: ! empty($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : null,
            settings_json: $data['settings_json'] ?? null,
            status: isset($data['status'])
                ? (is_string($data['status']) ? UnitStatusEnum::from($data['status']) : $data['status'])
                : null
        );
    }
}
