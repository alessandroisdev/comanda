<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Enums\UserStatusEnum;

class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly UserStatusEnum $status = UserStatusEnum::ACTIVE
    ) {}

    /**
     * Instancia o DTO a partir de um array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: strtolower(trim($data['email'])),
            password: $data['password'],
            status: isset($data['status'])
                ? (is_string($data['status']) ? UserStatusEnum::from($data['status']) : $data['status'])
                : UserStatusEnum::ACTIVE
        );
    }
}
