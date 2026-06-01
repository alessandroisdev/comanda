<?php

declare(strict_types=1);

namespace App\DTOs\Customer;

use App\Enums\CustomerStatusEnum;
use Illuminate\Support\Carbon;

class UpdateCustomerDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly ?string $phone = null,
        public readonly ?string $document = null,
        public readonly ?Carbon $birth_date = null,
        public readonly bool $marketing_opt_in = false,
        public readonly CustomerStatusEnum $status = CustomerStatusEnum::ACTIVE
    ) {}

    /**
     * Cria o DTO a partir de um array sanitizado ou request data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            email: strtolower(trim($data['email'])),
            password: ! empty($data['password']) ? $data['password'] : null,
            phone: isset($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : null,
            document: isset($data['document']) ? preg_replace('/[^0-9]/', '', $data['document']) : null,
            birth_date: ! empty($data['birth_date']) ? Carbon::parse($data['birth_date']) : null,
            marketing_opt_in: (bool) ($data['marketing_opt_in'] ?? false),
            status: isset($data['status'])
                ? (is_string($data['status']) ? CustomerStatusEnum::from($data['status']) : $data['status'])
                : CustomerStatusEnum::ACTIVE
        );
    }
}
