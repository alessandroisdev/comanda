<?php

declare(strict_types=1);

namespace App\DTOs\Company;

use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;

class UpdateCompanyDTO
{
    public function __construct(
        public readonly string $legal_name,
        public readonly string $trade_name,
        public readonly DocumentTypeEnum $document_type,
        public readonly string $document_number,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $timezone,
        public readonly string $currency,
        public readonly string $language,
        public readonly ?string $logo = null,
        public readonly ?array $settings_json = null,
        public readonly ?CompanyStatusEnum $status = null
    ) {}

    /**
     * Cria o DTO a partir de um array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            legal_name: $data['legal_name'],
            trade_name: $data['trade_name'],
            document_type: is_string($data['document_type']) 
                ? DocumentTypeEnum::from($data['document_type']) 
                : $data['document_type'],
            document_number: preg_replace('/[^0-9]/', '', $data['document_number']),
            email: strtolower(trim($data['email'])),
            phone: preg_replace('/[^0-9]/', '', $data['phone']),
            timezone: $data['timezone'] ?? 'America/Sao_Paulo',
            currency: $data['currency'] ?? 'BRL',
            language: $data['language'] ?? 'pt_BR',
            logo: $data['logo'] ?? null,
            settings_json: $data['settings_json'] ?? null,
            status: isset($data['status'])
                ? (is_string($data['status']) ? CompanyStatusEnum::from($data['status']) : $data['status'])
                : null
        );
    }
}
