<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Product;
use App\Services\SSE\SseQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeliveryCheckoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_open_delivery_view()
    {
        $this->withoutExceptionHandling();
        $company = Company::factory()->create();

        $response = $this->get(route('public.menu.delivery', ['company_id' => $company->id]));

        $response->assertStatus(200);
        $response->assertViewIs('public.menu.delivery');
    }

    #[Test]
    public function it_can_calculate_frete_via_endpoint()
    {
        $response = $this->getJson('/api/v1/delivery/frete?cep=01310-100');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'frete_cents' => 1000,
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
            ]);
    }

    #[Test]
    public function it_can_process_delivery_checkout_with_coupon_and_gateway_payment()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Pizzas',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'PIZZA-01',
            'name' => 'Pizza Calabresa',
            'price_cents' => 5000, // R$ 50,00
            'status' => 'active',
        ]);

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'DELIVERY10',
            'type' => 'percentage',
            'value' => 10, // 10%
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/delivery/checkout', [
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 2], // R$ 100,00 subtotal
            ],
            'customer_name' => 'Jane Doe',
            'customer_phone' => '11977777777',
            'customer_email' => 'jane@example.com',
            'customer_cpf' => '123.456.789-00',
            'street' => 'Rua Augusta',
            'number' => '1500',
            'complement' => 'Apto 42',
            'neighborhood' => 'Consolação',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01305-100',
            'delivery_fee' => 12.00, // R$ 12,00 frete
            'coupon_code' => 'DELIVERY10', // 10% de R$ 100 = R$ 10 de desc
            'payment_method' => 'pix',
            'gateway' => 'asaas',
            'lgpd_consent' => true, // consentimento opcional
        ]);

        // Total esperado: 100 (subtotal) + 12 (frete) - 10 (desconto) = 102 (R$ 102,00 ou 10200 centavos)
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotNull($response->json('payment_data.transaction_id'));

        // Valida que o pedido e comanda local do totem foram salvos com o valor correto
        $this->assertDatabaseHas('orders', [
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'subtotal_cents' => 10000,
            'discount_cents' => 1000,
            'total_cents' => 10200,
            'status' => 'draft', // Fica em draft até pagamento ser confirmado
        ]);

        // Valida que os dados pessoais de entrega e CPF foram cadastrados em conformidade LGPD
        $this->assertDatabaseHas('customers', [
            'document' => '123.456.789-00',
            'email' => 'jane@example.com',
        ]);

        // Valida o registro da base legal obrigatória de execução de contrato e consentimento opcional
        $this->assertDatabaseHas('privacy_audit_logs', [
            'action' => 'privacy.legal_basis',
        ]);
        $this->assertDatabaseHas('privacy_audit_logs', [
            'action' => 'privacy.consent_granted',
        ]);
    }

    #[Test]
    public function it_can_process_webhook_and_transition_order_status()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $session = OrderSession::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'opened_by_employee_id' => $employee->id,
            'people_count' => 1,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'order_number' => 'ORD-DEL-WEB',
            'status' => OrderStatusEnum::DRAFT,
        ]);

        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $deliveryOrder = DeliveryOrder::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'recipient_name' => 'Webhook Recipient',
            'recipient_phone' => '11999999999',
            'street' => 'Av Paulista',
            'number' => '1000',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01310-100',
            'delivery_fee' => 10.00,
            'status' => 'pending',
            'tracking_code' => 'asaas_tx_webhook_test',
        ]);

        Cache::forget('sse_events:admin.orders');

        // Dispara a chamada do Webhook fingindo ser o Asaas confirmando o pagamento da transação
        $response = $this->postJson('/api/v1/payments/webhooks/asaas', [
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => [
                'id' => 'asaas_tx_webhook_test',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Valida que o delivery order mudou para confirmed
        $deliveryOrder->refresh();
        $this->assertEquals('confirmed', $deliveryOrder->status);

        // Valida que o order mudou para SENT_TO_KITCHEN (enviado à produção)
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::SENT_TO_KITCHEN, $order->status);

        // Valida o SSE reativo publicado para a produção da cozinha
        $events = SseQueueService::pull('admin.orders');
        $this->assertNotEmpty($events);
        $eventTypes = array_map(fn ($e) => $e['event'], $events);
        $this->assertContains('orders.sent_to_kitchen', $eventTypes);
        $this->assertContains('order.confirmed', $eventTypes);
    }

    #[Test]
    public function it_fails_delivery_checkout_if_items_empty()
    {
        $this->withoutMiddleware();
        $response = $this->postJson('/api/v1/delivery/checkout', [
            'items' => [],
            'customer_name' => 'John Doe',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Carrinho vazio.',
            ]);
    }

    #[Test]
    public function it_fails_delivery_checkout_if_employee_missing()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Sucos',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'SUCO-01',
            'name' => 'Suco de Laranja',
            'price_cents' => 800,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/delivery/checkout', [
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 1],
            ],
            'customer_name' => 'Jane Doe',
            'customer_phone' => '11977777777',
            'customer_email' => 'jane@example.com',
            'customer_cpf' => '123.456.789-00',
            'street' => 'Rua Augusta',
            'number' => '1500',
            'neighborhood' => 'Consolação',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01305-100',
            'delivery_fee' => 10.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Delivery inoperante: sem funcionários.',
            ]);
    }

    #[Test]
    public function it_fails_delivery_checkout_with_invalid_coupon()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Massas',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'MASS-01',
            'name' => 'Lasanha',
            'price_cents' => 4000,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/delivery/checkout', [
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 1],
            ],
            'customer_name' => 'Jane Doe',
            'customer_phone' => '11977777777',
            'customer_email' => 'jane@example.com',
            'customer_cpf' => '123.456.789-00',
            'street' => 'Rua Augusta',
            'number' => '1500',
            'neighborhood' => 'Consolação',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01305-100',
            'delivery_fee' => 10.00,
            'coupon_code' => 'INVALIDCOUPON',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true, // Checkout prossegue, cupom apenas não é aplicado
            ]);
    }

    #[Test]
    public function it_can_calculate_frete_with_different_cep()
    {
        $response = $this->getJson('/api/v1/delivery/frete?cep=22021-001');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'frete_cents' => 1000,
            ]);
    }

    #[Test]
    public function it_fails_if_coupon_code_does_not_exist()
    {
        $response = $this->getJson('/api/v1/coupons/validate?code=NONEXISTENT');

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Cupom não encontrado ou inativo.',
            ]);
    }

    #[Test]
    public function it_fails_webhook_with_invalid_event()
    {
        $this->withoutMiddleware();
        $response = $this->postJson('/api/v1/payments/webhooks/asaas', [
            'event' => 'PAYMENT_REFUNDED',
            'payment' => [
                'id' => 'asaas_tx_invalid_event_test',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    #[Test]
    public function it_fails_webhook_with_unknown_gateway()
    {
        $this->withoutMiddleware();
        $response = $this->postJson('/api/v1/payments/webhooks/unknown_gateway', [
            'event' => 'PAYMENT_CONFIRMED',
        ]);

        $response->assertStatus(500);
    }

    #[Test]
    public function it_fails_tablet_view_with_invalid_uuid()
    {
        $response = $this->get('/cardapio/m/00000000-0000-0000-0000-000000000000');
        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_process_delivery_checkout_without_optional_marketing_consent()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Bebidas',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'COCA-99',
            'name' => 'Coca 2L',
            'price_cents' => 1200,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/delivery/checkout', [
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 1],
            ],
            'customer_name' => 'John Doe Without Consent',
            'customer_phone' => '11988888888',
            'customer_email' => 'noconsent@example.com',
            'customer_cpf' => '987.654.321-00',
            'street' => 'Av. Paulista',
            'number' => '2000',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01310-200',
            'delivery_fee' => 10.00,
            'lgpd_consent' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('customers', [
            'document' => '987.654.321-00',
            'email' => 'noconsent@example.com',
        ]);

        // Valida que o log de base legal de execução de contrato existe
        $this->assertDatabaseHas('privacy_audit_logs', [
            'action' => 'privacy.legal_basis',
        ]);

        // E o consentimento de marketing NÃO existe para este cliente no banco de dados de logs
        $this->assertDatabaseMissing('privacy_audit_logs', [
            'action' => 'privacy.consent_granted',
            'entity_uuid' => Customer::where('document', '987.654.321-00')->first()->uuid,
        ]);
    }

    #[Test]
    public function it_fails_frete_with_empty_cep()
    {
        $response = $this->getJson('/api/v1/delivery/frete?cep=');

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'CEP inválido.',
            ]);
    }

    #[Test]
    public function it_does_not_create_delivery_customer_with_predictable_or_default_password()
    {
        $this->withoutMiddleware();
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id, 'unit_id' => $unit->id]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Bebidas',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'code' => 'SUCO-01',
            'name' => 'Suco de Laranja',
            'price_cents' => 800,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/delivery/checkout', [
            'items' => [
                ['uuid' => $product->uuid, 'quantity' => 1],
            ],
            'customer_name' => 'Safe Customer',
            'customer_phone' => '11999999999',
            'customer_email' => 'safe.customer@example.com',
            'customer_cpf' => '444.555.666-77',
            'street' => 'Av. Paulista',
            'number' => '1000',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01310-100',
            'delivery_fee' => 10.00,
            'lgpd_consent' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        /** @var Customer $customer */
        $customer = Customer::where('document', '444.555.666-77')->firstOrFail();

        // Garante que a senha gerada NÃO é password123 nem vazia
        $this->assertNotEmpty($customer->password);
        $this->assertFalse(Hash::check('password123', $customer->password));
        $this->assertFalse(Hash::check('', $customer->password));

        // A senha deve ser uma hash bcrypt/argon forte aleatória e inutilizável
        $this->assertStringStartsWith('$', $customer->password);
    }
}
