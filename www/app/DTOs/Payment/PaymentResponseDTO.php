<?php

declare(strict_types=1);

namespace App\DTOs\Payment;

class PaymentResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $transactionId,
        public string $status,
        public string $paymentMethod,
        public ?string $qrCodeUrl = null,
        public ?string $qrCodeBase64 = null,
        public ?string $paymentUrl = null,
        public ?string $errorMessage = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (bool) $data['success'],
            $data['transaction_id'] ?? null,
            $data['status'],
            $data['payment_method'],
            $data['qr_code_url'] ?? null,
            $data['qr_code_base64'] ?? null,
            $data['payment_url'] ?? null,
            $data['error_message'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'qr_code_url' => $this->qrCodeUrl,
            'qr_code_base64' => $this->qrCodeBase64,
            'payment_url' => $this->paymentUrl,
            'error_message' => $this->errorMessage,
        ];
    }
}
