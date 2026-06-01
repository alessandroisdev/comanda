<?php

declare(strict_types=1);

namespace App\Services\Payment\Drivers;

use App\DTOs\Payment\PaymentRequestDTO;
use App\DTOs\Payment\PaymentResponseDTO;
use App\Services\Payment\GatewayInterface;
use Illuminate\Support\Facades\Log;

class AsaasGateway implements GatewayInterface
{
    public function charge(PaymentRequestDTO $dto): PaymentResponseDTO
    {
        Log::info('[Gateway Asaas] Processando cobrança...', [
            'amount' => $dto->amount,
            'customer_cpf' => substr($dto->customerCpf, 0, 3) . '***', // Mascaramento de privacidade LGPD
            'order_id' => $dto->orderId,
        ]);

        // Simulação de transação PIX ou Cartão no Asaas
        $success = $dto->amount > 0;
        $transactionId = 'asaas_tx_' . bin2hex(random_bytes(8));

        return new PaymentResponseDTO(
            success: $success,
            transactionId: $success ? $transactionId : null,
            status: $success ? 'pending' : 'failed',
            paymentMethod: $dto->paymentMethod,
            qrCodeUrl: 'https://asaas.com/pix/qr/' . $transactionId,
            qrCodeBase64: 'asaas_base64_image_data...',
            errorMessage: $success ? null : 'Valor de cobrança deve ser maior que zero.'
        );
    }

    public function refund(string $transactionId): bool
    {
        Log::info("[Gateway Asaas] Solicitando reembolso para transação: {$transactionId}");
        return true;
    }

    public function handleWebhook(array $payload): PaymentResponseDTO
    {
        Log::info('[Gateway Asaas] Webhook processado', $payload);

        $event = $payload['event'] ?? 'PAYMENT_CONFIRMED';
        $transactionId = $payload['payment']['id'] ?? 'asaas_tx_simulated';
        $status = 'failed';

        if ($event === 'PAYMENT_CONFIRMED' || $event === 'PAYMENT_RECEIVED') {
            $status = 'paid';
        } elseif ($event === 'PAYMENT_REFUNDED') {
            $status = 'refunded';
        }

        return new PaymentResponseDTO(
            success: true,
            transactionId: $transactionId,
            status: $status,
            paymentMethod: 'pix'
        );
    }
}
