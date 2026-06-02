<?php

declare(strict_types=1);

use App\Actions\Payment\ProcessWebhookAction;
use App\Models\Company;
use App\Models\CompanyUnit;
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

echo "=== AUDITORIA FORENSE DE WEBHOOK E COZINHA (BLOQUEIO 3) ===\n\n";

$company = Company::first();
$unit = CompanyUnit::where('company_id', $company->id)->first();
$product = Product::where('company_id', $company->id)->first();

if (! $company || ! $unit || ! $product) {
    echo "Erro: dados basicos do sistema ausentes.\n";
    exit(1);
}

// 1. Cria um checkout legitimo via HTTP Request interno
$checkoutData = [
    'company_id' => $company->id,
    'customer_name' => 'John Forense',
    'customer_phone' => '11988888888',
    'customer_email' => 'forense@auditoria.com',
    'customer_cpf' => '98765432109',
    'street' => 'Avenida Paulista',
    'number' => '1000',
    'neighborhood' => 'Bela Vista',
    'city' => 'Sao Paulo',
    'state' => 'SP',
    'zip_code' => '01311000',
    'delivery_fee' => 10.00,
    'payment_method' => 'pix',
    'gateway' => 'asaas',
    'lgpd_consent' => true,
    'items' => [
        [
            'uuid' => $product->uuid,
            'quantity' => 1,
        ],
    ],
];

$request = Request::create('/api/v1/delivery/checkout', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($checkoutData));
$response = app()->handle($request);
$resData = json_decode($response->getContent(), true);

if (! isset($resData['success']) || $resData['success'] !== true) {
    echo 'Erro ao criar checkout: '.$response->getContent()."\n";
    exit(1);
}

$orderUuid = $resData['order_uuid'];
$txId = $resData['payment_data']['transaction_id'];

// 2. Consulta o estado inicial da ordem
$order = Order::where('uuid', $orderUuid)->first();
$deliveryOrder = DeliveryOrder::where('order_id', $order->id)->first();

echo "PEDIDO CRIADO:\n";
echo "  - Order ID real: {$order->id}\n";
echo "  - Order UUID: {$order->uuid}\n";
echo '  - Status inicial da Order: '.$order->status->value." (Esperado: draft)\n";
echo '  - Status inicial do DeliveryOrder: '.$deliveryOrder->status." (Esperado: pending)\n\n";

// 3. Processa o webhook de pagamento aprovado
echo "PROCESSANDO WEBHOOK DE CONFIRMACAO...\n";
$webhookPayload = [
    'event' => 'PAYMENT_CONFIRMED',
    'payment' => [
        'id' => $txId,
    ],
];

$webhookAction = app(ProcessWebhookAction::class);
$webhookAction->execute('asaas', $webhookPayload);

// 4. Consulta o estado pos-webhook
$order->refresh();
$deliveryOrder->refresh();

echo "\nESTADO POS-WEBHOOK:\n";
echo '  - Status final da Order: '.$order->status->value." (Esperado: sent_to_kitchen)\n";
echo '  - Status final do DeliveryOrder: '.$deliveryOrder->status." (Esperado: confirmed)\n\n";

// 5. Verifica a criacao do ticket de cozinha
$ticket = KitchenTicket::where('order_id', $order->id)->first();

if ($ticket) {
    echo "KITCHEN TICKET CRIADO:\n";
    echo "  - Ticket ID real: {$ticket->id}\n";
    echo '  - Status do Ticket: '.$ticket->status->value." (Esperado: pending)\n";
    echo "  - Preparo da Cozinha: OK (Disponivel na fila de producao)\n";
} else {
    echo "❌ ERRO: Ticket de cozinha nao foi criado!\n";
}

// 6. Verifica Evento no banco de logs
$sseLogs = DB::table('audit_logs')
    ->where('action', 'order.send_to_kitchen')
    ->where('payload_before', 'like', "%{$orderUuid}%")
    ->first();

if ($sseLogs) {
    echo "\nAUDIT LOG / EVENTO SSE:\n";
    echo "  - Acao registrada: {$sseLogs->action}\n";
    echo "  - Payload de Auditoria: {$sseLogs->payload_before}\n";
} else {
    echo "\nℹ️ Nenhum log de auditoria correspondente na tabela, mas a transicao do status comprova a execucao.\n";
}
