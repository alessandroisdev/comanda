<?php

declare(strict_types=1);

namespace App\Services\Payment\Drivers;

use App\DTOs\Payment\PaymentRequestDTO;
use App\DTOs\Payment\PaymentResponseDTO;
use App\Services\Payment\GatewayInterface;
use Illuminate\Support\Facades\Log;

class PagSeguroGateway implements GatewayInterface
{
    public function charge(PaymentRequestDTO $dto): PaymentResponseDTO
    {
        Log::info('[Gateway PagSeguro] Criando cobrança...', [
            'amount' => $dto->amount,
            'order_id' => $dto->orderId,
        ]);

        $success = $dto->amount > 0;
        $transactionId = 'ps_tx_' . bin2hex(random_bytes(8));

        return new PaymentResponseDTO(
            success: $success,
            transactionId: $success ? $transactionId : null,
            status: $success ? 'pending' : 'failed',
            paymentMethod: $dto->paymentMethod,
            paymentUrl: 'https://pagseguro.uol.com.br/checkout/' . $transactionId,
            errorMessage: $success ? null : 'Erro de validação de valor no PagSeguro.'
        );
    }

    public function refund(string $transactionId): bool
    {
        Log::info("[Gateway PagSeguro] Estornando transação: {$transactionId}");
        return true;
    }

    public function handleWebhook(array $payload): PaymentResponseDTO
    {
        Log::info('[Gateway PagSeguro] Webhook executado', $payload);

        $statusValue = $payload['status'] ?? 'PENDING';
        $transactionId = $payload['id'] ?? 'ps_tx_simulated';
        $status = 'pending';

        if ($statusValue === 'PAID' || $statusValue === 'AUTHORIZED') {
            $status = 'paid';
        }

        return new PaymentResponseDTO(
            success: true,
            transactionId: $transactionId,
            status: $status,
            paymentMethod: 'credit_card'
        );
    }
}
