<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\DTOs\Payment\PaymentRequestDTO;
use App\DTOs\Payment\PaymentResponseDTO;

interface GatewayInterface
{
    /**
     * Processa a cobrança financeira baseada no DTO de requisição.
     */
    public function charge(PaymentRequestDTO $dto): PaymentResponseDTO;

    /**
     * Efetua o reembolso de uma transação financeira.
     */
    public function refund(string $transactionId): bool;

    /**
     * Processa e padroniza o payload recebido via webhook assíncrono do provedor.
     */
    public function handleWebhook(array $payload): PaymentResponseDTO;
}
