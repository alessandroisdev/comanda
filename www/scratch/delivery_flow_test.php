<?php

declare(strict_types=1);

use App\Actions\Payment\ProcessWebhookAction;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Coupon;
use App\Models\DeliveryOrder;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$outputBuffer = "=== AUDITORIA DE DELIVERY E FLUXO FINANCEIRO (ETAPA P6) ===\n\n";

// 1. Setup inicial
$company = Company::first();
$unit = CompanyUnit::where('company_id', $company->id)->first();
$product = Product::where('company_id', $company->id)->first();

if (! $company || ! $unit || ! $product) {
    $outputBuffer .= "Dados do sistema incompletos para auditoria. Abortando.\n";
    exit(1);
}

$outputBuffer .= "Produto para teste: {$product->name} (Preço: R$ ".number_format($product->price_cents / 100, 2, ',', '.').")\n\n";

// 2. Validação de Frete
$outputBuffer .= "1. Testando cálculo de frete (ViaCEP integrado)...\n";
$freteRequest = Request::create('/api/v1/delivery/frete', 'GET', ['cep' => '01311000']);
$freteResponse = app()->handle($freteRequest);
$freteData = json_decode($freteResponse->getContent(), true);

if ($freteData['success'] && $freteData['frete_cents'] === 1000) {
    $outputBuffer .= "  - Frete calculado com sucesso: R$ 10,00 | Cidade: {$freteData['localidade']}\n";
} else {
    $outputBuffer .= "  - Falha ao obter dados de frete.\n";
}

// 3. Validação de Cupons
$outputBuffer .= "\n2. Auditando regras de cupons...\n";

// Limpar cupons de teste anteriores se houver
Coupon::whereIn('code', ['DESC10', 'EXPIRADO', 'MINIMO'])->forceDelete();

// Criar cupons para testes
$couponValid = Coupon::create([
    'company_id' => $company->id,
    'code' => 'DESC10',
    'type' => 'percent',
    'value' => 10,
    'min_order_amount_cents' => 1000,
    'is_active' => true,
]);

$couponExpired = Coupon::create([
    'company_id' => $company->id,
    'code' => 'EXPIRADO',
    'type' => 'fixed',
    'value' => 500,
    'min_order_amount_cents' => 1000,
    'expires_at' => now()->subDay(),
    'is_active' => true,
]);

$couponMin = Coupon::create([
    'company_id' => $company->id,
    'code' => 'MINIMO',
    'type' => 'fixed',
    'value' => 1000,
    'min_order_amount_cents' => 10000, // R$ 100,00 min
    'is_active' => true,
]);

// Testar cada um contra um subtotal de R$ 50,00 (5000 cents)
$subtotalTest = 5000;
$outputBuffer .= "  - Subtotal base de teste: R$ 50,00\n";
$outputBuffer .= '  - Cupom DESC10 (10%): Desconto calculado: R$ '.number_format($couponValid->calculateDiscount($subtotalTest) / 100, 2, ',', '.')." (Esperado: R$ 5,00)\n";
$outputBuffer .= '  - Cupom EXPIRADO (R$ 5,00): Desconto calculado: R$ '.number_format($couponExpired->calculateDiscount($subtotalTest) / 100, 2, ',', '.')." (Esperado: R$ 0,00)\n";
$outputBuffer .= '  - Cupom MINIMO (R$ 10,00 min R$ 100,00): Desconto calculado: R$ '.number_format($couponMin->calculateDiscount($subtotalTest) / 100, 2, ',', '.')." (Esperado: R$ 0,00)\n";

// 4. Teste de Vulnerabilidade Financeira: Quantidades Negativas
$outputBuffer .= "\n3. Testando Vulnerabilidade Financeira de Quantidade Negativa...\n";

$badCheckoutData = [
    'company_id' => $company->id,
    'customer_name' => 'Alvo Vulneravel',
    'customer_phone' => '11999999999',
    'customer_email' => 'alvo@vulneravel.com',
    'customer_cpf' => '12345678909',
    'street' => 'Rua Teste',
    'number' => '123',
    'neighborhood' => 'Centro',
    'city' => 'Sao Paulo',
    'state' => 'SP',
    'zip_code' => '01234000',
    'delivery_fee' => 10.00,
    'payment_method' => 'pix',
    'gateway' => 'asaas',
    'lgpd_consent' => true,
    'items' => [
        [
            'uuid' => $product->uuid,
            'quantity' => -2, // Quantidade negativa!
        ],
    ],
];

$checkoutRequest = Request::create('/api/v1/delivery/checkout', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($badCheckoutData));
try {
    $checkoutResponse = app()->handle($checkoutRequest);
    $resData = json_decode($checkoutResponse->getContent(), true);
    if (isset($resData['success']) && $resData['success'] === true) {
        $outputBuffer .= "  - ⚠️ ALERTA DE SEGURANÇA: Checkout com quantidade negativa foi processado com sucesso!\n";
        $orderCreated = Order::where('uuid', $resData['order_uuid'])->first();
        $outputBuffer .= '    * Total do Pedido Criado: R$ '.number_format($orderCreated->total_cents / 100, 2, ',', '.')."\n";
        $outputBuffer .= '    * Subtotal: R$ '.number_format($orderCreated->subtotal_cents / 100, 2, ',', '.')."\n";
        $outputBuffer .= "    * Detalhes: A falta de validação sanitária no carrinho permitiu subtotal negativo/zerado.\n";
    } else {
        $outputBuffer .= '  - Sucesso: O checkout de quantidade negativa foi bloqueado (Mensagem: '.($resData['message'] ?? 'Nenhuma').")\n";
    }
} catch (Throwable $e) {
    $outputBuffer .= '  - Bloqueado com exceção: '.$e->getMessage()."\n";
}

// 5. Fluxo de Checkout Legítimo e Webhook
$outputBuffer .= "\n4. Executando Checkout Legítimo e Integração de Webhook...\n";

$goodCheckoutData = [
    'company_id' => $company->id,
    'customer_name' => 'Cliente Auditoria',
    'customer_phone' => '11988888888',
    'customer_email' => 'cliente@auditoria.com',
    'customer_cpf' => '98765432109',
    'street' => 'Avenida Paulista',
    'number' => '1000',
    'neighborhood' => 'Bela Vista',
    'city' => 'Sao Paulo',
    'state' => 'SP',
    'zip_code' => '01311000',
    'delivery_fee' => 10.00,
    'coupon_code' => 'DESC10',
    'payment_method' => 'pix',
    'gateway' => 'asaas',
    'lgpd_consent' => true,
    'items' => [
        [
            'uuid' => $product->uuid,
            'quantity' => 2, // Quantidade positiva
        ],
    ],
];

$goodRequest = Request::create('/api/v1/delivery/checkout', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($goodCheckoutData));
$goodResponse = app()->handle($goodRequest);
$goodResData = json_decode($goodResponse->getContent(), true);

if ($goodResData['success'] === true) {
    $orderUuid = $goodResData['order_uuid'];
    $txId = $goodResData['payment_data']['transaction_id'];
    $outputBuffer .= "  - Checkout criado com sucesso! Order UUID: {$orderUuid} | Transaction ID: {$txId}\n";

    $orderDb = Order::where('uuid', $orderUuid)->first();
    $deliveryDb = DeliveryOrder::where('order_id', $orderDb->id)->first();

    $outputBuffer .= "    * Status inicial do Pedido: {$orderDb->status->value} (Esperado: draft)\n";
    $outputBuffer .= "    * Status inicial do Delivery: {$deliveryDb->status} (Esperado: pending)\n";
    $outputBuffer .= '    * Subtotal calculado: R$ '.number_format($orderDb->subtotal_cents / 100, 2, ',', '.')."\n";
    $outputBuffer .= '    * Desconto calculado (10%): R$ '.number_format($orderDb->discount_cents / 100, 2, ',', '.')."\n";
    $outputBuffer .= '    * Frete cobrado: R$ '.number_format($deliveryDb->delivery_fee, 2, ',', '.')."\n";
    $outputBuffer .= '    * Total final do pedido: R$ '.number_format($orderDb->total_cents / 100, 2, ',', '.')."\n";

    // 6. Testar Webhook de Confirmação (ProcessWebhookAction)
    $outputBuffer .= "\n5. Disparando Webhook de Confirmação do Asaas...\n";
    $webhookPayload = [
        'event' => 'PAYMENT_CONFIRMED',
        'payment' => [
            'id' => $txId,
        ],
    ];

    $webhookAction = app(ProcessWebhookAction::class);
    $webhookAction->execute('asaas', $webhookPayload);

    $orderDb->refresh();
    $deliveryDb->refresh();
    $outputBuffer .= "    * Status pós-webhook do Pedido: {$orderDb->status->value} (Esperado: pending)\n";
    $outputBuffer .= "    * Status pós-webhook do Delivery: {$deliveryDb->status} (Esperado: confirmed)\n";

    // Verificar se foi gerado o Ticket de Cozinha
    $ticketExists = KitchenTicket::where('order_id', $orderDb->id)->exists();
    $outputBuffer .= '    * Ticket de Cozinha gerado: '.($ticketExists ? 'SIM' : 'NÃO')." (Esperado: SIM)\n";

    // 7. Testar Webhook Duplicado (Idempotência)
    $outputBuffer .= "\n6. Disparando Webhook DUPLICADO com mesma transação (Auditoria de Idempotência)...\n";
    $logsBefore = DB::table('audit_logs')->where('action', 'payment.webhook_confirmed')->count();

    // Dispara novamente
    $webhookAction->execute('asaas', $webhookPayload);

    $logsAfter = DB::table('audit_logs')->where('action', 'payment.webhook_confirmed')->count();
    $outputBuffer .= "    * Chamadas webhook registradas no log: Antes={$logsBefore}, Depois={$logsAfter}\n";
    if ($logsAfter > $logsBefore) {
        $outputBuffer .= "    * ⚠️ RISCO DE IDEMPOTÊNCIA: Processamento duplicou registros de auditoria financeira.\n";
    } else {
        $outputBuffer .= "    * Sucesso: Processamento de webhook duplicado bloqueado / idempotente.\n";
    }
} else {
    $outputBuffer .= '  - Falha ao realizar checkout legítimo: '.$goodResData['message']."\n";
}

file_put_contents(__DIR__.'/delivery_flow_result.txt', $outputBuffer);
echo "Auditoria de Delivery concluída e salva em scratch/delivery_flow_result.txt\n";
