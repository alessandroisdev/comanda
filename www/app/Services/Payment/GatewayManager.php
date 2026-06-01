<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Services\Payment\Drivers\AsaasGateway;
use App\Services\Payment\Drivers\MercadoPagoGateway;
use App\Services\Payment\Drivers\PagSeguroGateway;
use App\Services\Payment\Drivers\StripeGateway;
use InvalidArgumentException;

class GatewayManager
{
    /**
     * Resolve e retorna o driver de gateway solicitado.
     *
     * @param  string  $driver  Nome do provedor (asaas, mercadopago, pagseguro, stripe)
     *
     * @throws InvalidArgumentException
     */
    public function driver(string $driver): GatewayInterface
    {
        return match (strtolower($driver)) {
            'asaas' => new AsaasGateway,
            'mercadopago', 'mercado_pago' => new MercadoPagoGateway,
            'pagseguro', 'pag_seguro' => new PagSeguroGateway,
            'stripe' => new StripeGateway,
            default => throw new InvalidArgumentException("Gateway driver [{$driver}] não é suportado pelo ecossistema Comanda."),
        };
    }
}
