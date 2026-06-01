<?php

declare(strict_types=1);

namespace App\DTOs\Payment;

class PaymentRequestDTO
{
    public function __construct(
        public float $amount,
        public string $currency,
        public string $paymentMethod,
        public string $customerName,
        public string $customerEmail,
        public string $customerCpf,
        public string $orderId,
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (float) $data['amount'],
            $data['currency'] ?? 'BRL',
            $data['payment_method'],
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_cpf'],
            $data['order_id'],
            $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->paymentMethod,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_cpf' => $this->customerCpf,
            'order_id' => $this->orderId,
            'metadata' => $this->metadata,
        ];
    }
}
