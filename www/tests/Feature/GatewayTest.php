<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\Payment\PaymentRequestDTO;
use App\Services\Payment\Drivers\AsaasGateway;
use App\Services\Payment\Drivers\MercadoPagoGateway;
use App\Services\Payment\Drivers\PagSeguroGateway;
use App\Services\Payment\Drivers\StripeGateway;
use App\Services\Payment\GatewayManager;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GatewayTest extends TestCase
{
    private GatewayManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new GatewayManager;
    }

    #[Test]
    public function it_resolves_all_drivers_correctly()
    {
        $this->assertInstanceOf(AsaasGateway::class, $this->manager->driver('asaas'));
        $this->assertInstanceOf(MercadoPagoGateway::class, $this->manager->driver('mercadopago'));
        $this->assertInstanceOf(PagSeguroGateway::class, $this->manager->driver('pagseguro'));
        $this->assertInstanceOf(StripeGateway::class, $this->manager->driver('stripe'));
    }

    #[Test]
    public function it_throws_exception_for_unsupported_driver()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->manager->driver('invalid_gateway');
    }

    #[Test]
    public function stripe_charge_success_scenario()
    {
        $driver = $this->manager->driver('stripe');
        $dto = new PaymentRequestDTO(
            amount: 150.00,
            currency: 'BRL',
            paymentMethod: 'credit_card',
            customerName: 'Cliente Stripe',
            customerEmail: 'stripe@comanda.com.br',
            customerCpf: '12345678901',
            orderId: 'order_123'
        );

        $response = $driver->charge($dto);

        $this->assertTrue($response->success);
        $this->assertNotNull($response->transactionId);
        $this->assertEquals('pending', $response->status);
        $this->assertNotNull($response->paymentUrl);
    }

    #[Test]
    public function stripe_charge_fails_for_zero_amount()
    {
        $driver = $this->manager->driver('stripe');
        $dto = new PaymentRequestDTO(
            amount: 0.00,
            currency: 'BRL',
            paymentMethod: 'credit_card',
            customerName: 'Cliente Stripe',
            customerEmail: 'stripe@comanda.com.br',
            customerCpf: '12345678901',
            orderId: 'order_123'
        );

        $response = $driver->charge($dto);

        $this->assertFalse($response->success);
        $this->assertNull($response->transactionId);
        $this->assertEquals('failed', $response->status);
    }

    #[Test]
    public function asaas_charge_success_scenario()
    {
        $driver = $this->manager->driver('asaas');
        $dto = new PaymentRequestDTO(
            amount: 75.50,
            currency: 'BRL',
            paymentMethod: 'pix',
            customerName: 'Cliente Asaas',
            customerEmail: 'asaas@comanda.com.br',
            customerCpf: '98765432100',
            orderId: 'order_456'
        );

        $response = $driver->charge($dto);

        $this->assertTrue($response->success);
        $this->assertNotNull($response->transactionId);
        $this->assertEquals('pending', $response->status);
        $this->assertNotNull($response->qrCodeUrl);
    }

    #[Test]
    public function mp_charge_success_scenario()
    {
        $driver = $this->manager->driver('mercadopago');
        $dto = new PaymentRequestDTO(
            amount: 99.90,
            currency: 'BRL',
            paymentMethod: 'pix',
            customerName: 'Cliente MP',
            customerEmail: 'mp@comanda.com.br',
            customerCpf: '11122233344',
            orderId: 'order_789'
        );

        $response = $driver->charge($dto);

        $this->assertTrue($response->success);
        $this->assertNotNull($response->transactionId);
        $this->assertEquals('pending', $response->status);
    }

    #[Test]
    public function pagseguro_charge_success_scenario()
    {
        $driver = $this->manager->driver('pagseguro');
        $dto = new PaymentRequestDTO(
            amount: 45.00,
            currency: 'BRL',
            paymentMethod: 'credit_card',
            customerName: 'Cliente PS',
            customerEmail: 'ps@comanda.com.br',
            customerCpf: '55566677788',
            orderId: 'order_999'
        );

        $response = $driver->charge($dto);

        $this->assertTrue($response->success);
        $this->assertNotNull($response->transactionId);
        $this->assertEquals('pending', $response->status);
    }
}
