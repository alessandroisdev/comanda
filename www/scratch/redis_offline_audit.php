<?php

declare(strict_types=1);

use App\Models\Table;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== AUDITORIA DE RESILIENCIA COM REDIS OFFLINE (BLOQUEIO 5) ===\n\n";

$routes = [
    'Health Live' => '/api/health/live',
    'Health Ready' => '/api/health/ready',
    'Menu Publico' => '/api/v1/menu/categories',
    'Delivery CEP' => '/api/v1/delivery/frete?cep=01311000',
];

// 1. Testar chamadas HTTP locais no container
foreach ($routes as $name => $uri) {
    echo "Testando rota '$name' ($uri)...\n";
    $request = Request::create($uri, 'GET');

    try {
        $response = app()->handle($request);
        echo '  - Status HTTP: '.$response->getStatusCode()."\n";
        echo '  - Corpo da Resposta (resumido): '.substr($response->getContent(), 0, 180)."\n";
    } catch (Throwable $e) {
        echo '  - ❌ EXCECAO CAPTURADA: '.$e->getMessage()."\n";
        echo '  - File: '.$e->getFile().' Line: '.$e->getLine()."\n";
    }
    echo "\n";
}

// 2. Testar Tablet resolvendo Mesa com Redis offline
$table = Table::first();
if ($table) {
    $uri = '/api/v1/tablet/order';
    echo "Testando Post de Pedido do Tablet ($uri)...\n";

    $postData = [
        'table_uuid' => $table->public_uuid,
        'items' => [], // envia vazio so para testar se cai em 422 ou 200 tratável, e não em 500
    ];

    $request = Request::create($uri, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($postData));

    try {
        $response = app()->handle($request);
        echo '  - Status HTTP: '.$response->getStatusCode()."\n";
        echo '  - Corpo da Resposta: '.$response->getContent()."\n";
    } catch (Throwable $e) {
        echo '  - ❌ EXCECAO CAPTURADA: '.$e->getMessage()."\n";
    }
}
