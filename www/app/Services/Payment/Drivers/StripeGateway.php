<?php

declare(strict_types=1);

namespace App\Services\Payment\Drivers;

use App\DTOs\Payment\PaymentRequestDTO;
use App\DTOs\Payment\PaymentResponseDTO;
use App\Services\Payment\GatewayInterface;
use Illuminate\Support\Facades\Log;

class StripeGateway implements GatewayInterface
{
    public function charge(PaymentRequestDTO $dto): PaymentResponseDTO
    {
        Log::info('[Gateway Stripe] Criando Intent...', [
            'amount' => $dto->amount,
            'currency' => $dto->currency,
            'order_id' => $dto->orderId,
        ]);

        $success = $dto->amount > 0;
        $transactionId = 'pi_' . bin2hex(random_bytes(12));

        return new PaymentResponseDTO(
            success: $success,
            transactionId: $success ? $transactionId : null,
            status: $success ? 'pending' : 'failed',
            paymentMethod: $dto->paymentMethod,
            paymentUrl: 'https://checkout.stripe.com/pay/' . $transactionId,
            errorMessage: $success ? null : 'Invalid Stripe charge amount.'
        );
    }

    public function refund(string $transactionId): bool
    {
        Log::info("[Gateway Stripe] Refund para intent: {$transactionId}");
        return true;
    }

    public function handleWebhook(array $payload): PaymentResponseDTO
    {
        Log::info('[Gateway Stripe] Webhook executado', $payload);

        $type = $payload['type'] ?? 'payment_intent.succeeded';
        $transactionId = $payload['data']['object']['id'] ?? 'pi_simulated';
        $status = 'pending';

        if ($type === 'payment_intent.succeeded') {
            $status = 'paid';
        } elseif ($type === 'payment_intent.payment_failed') {
            $status = 'failed';
        }

        return new PaymentResponseDTO(
            success: true,
            transactionId: $transactionId,
            status: $status,
            paymentMethod: 'credit_card'
        );
    }
}
