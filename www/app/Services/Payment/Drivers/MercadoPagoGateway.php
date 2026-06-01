<?php

declare(strict_types=1);

namespace App\Services\Payment\Drivers;

use App\DTOs\Payment\PaymentRequestDTO;
use App\DTOs\Payment\PaymentResponseDTO;
use App\Services\Payment\GatewayInterface;
use Illuminate\Support\Facades\Log;

class MercadoPagoGateway implements GatewayInterface
{
    public function charge(PaymentRequestDTO $dto): PaymentResponseDTO
    {
        Log::info('[Gateway MercadoPago] Processando cobrança...', [
            'amount' => $dto->amount,
            'order_id' => $dto->orderId,
        ]);

        $success = $dto->amount > 0;
        $transactionId = 'mp_tx_' . bin2hex(random_bytes(8));

        return new PaymentResponseDTO(
            success: $success,
            transactionId: $success ? $transactionId : null,
            status: $success ? 'pending' : 'failed',
            paymentMethod: $dto->paymentMethod,
            qrCodeUrl: 'https://mercadopago.com/pix/qr/' . $transactionId,
            qrCodeBase64: 'mp_base64_image_data...',
            errorMessage: $success ? null : 'Falha ao gerar transação no Mercado Pago.'
        );
    }

    public function refund(string $transactionId): bool
    {
        Log::info("[Gateway MercadoPago] Reembolso solicitado: {$transactionId}");
        return true;
    }

    public function handleWebhook(array $payload): PaymentResponseDTO
    {
        Log::info('[Gateway MercadoPago] Webhook processado', $payload);

        $action = $payload['action'] ?? 'payment.created';
        $transactionId = $payload['data']['id'] ?? 'mp_tx_simulated';
        $status = 'pending';

        if ($action === 'payment.updated' && ($payload['data']['status'] ?? '') === 'approved') {
            $status = 'paid';
        }

        return new PaymentResponseDTO(
            success: true,
            transactionId: (string) $transactionId,
            status: $status,
            paymentMethod: 'pix'
        );
    }
}
