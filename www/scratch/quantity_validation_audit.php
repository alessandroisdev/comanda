<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== AUDITORIA FORENSE DE QUANTIDADE NEGATIVA (BLOQUEIO 4) ===\n\n";

$company = Company::first();
$product = Product::where('company_id', $company->id)->first();

if (! $company || ! $product) {
    echo "Erro: dados basicos ausentes.\n";
    exit(1);
}

$testQuantities = [0, -1, -2, -999];

foreach ($testQuantities as $qty) {
    echo "Testando quantidade: $qty\n";

    $checkoutData = [
        'company_id' => $company->id,
        'customer_name' => 'John Fraud',
        'customer_phone' => '11988888888',
        'customer_email' => 'fraud@auditoria.com',
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
                'quantity' => $qty, // Quantidade invalida
            ],
        ],
    ];

    $request = Request::create('/api/v1/delivery/checkout', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($checkoutData));

    // Contar registros antes
    $ordersCountBefore = Order::count();
    $itemsCountBefore = OrderItem::count();
    $finLogBefore = DB::table('audit_logs')->where('action', 'like', 'payment.%')->count();

    $response = app()->handle($request);
    $resData = json_decode($response->getContent(), true);

    // Contar registros depois
    $ordersCountAfter = Order::count();
    $itemsCountAfter = OrderItem::count();
    $finLogAfter = DB::table('audit_logs')->where('action', 'like', 'payment.%')->count();

    echo '  - Status HTTP: '.$response->getStatusCode()."\n";
    echo '  - Resposta JSON: '.$response->getContent()."\n";
    echo "  - Registros de Pedido (Antes/Depois): $ordersCountBefore / $ordersCountAfter\n";
    echo "  - Registros de Itens (Antes/Depois): $itemsCountBefore / $itemsCountAfter\n";
    echo "  - Registros Financeiros (Antes/Depois): $finLogBefore / $finLogAfter\n";

    $success = ($response->getStatusCode() === 422 && $ordersCountBefore === $ordersCountAfter && $itemsCountBefore === $itemsCountAfter && $finLogBefore === $finLogAfter);
    echo '  - Resultado do Bloco: '.($success ? 'PASS (Bloqueado corretamente)' : 'FAIL (Vazamento de pedido/item/financeiro)')."\n\n";
}
