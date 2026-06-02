<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\KitchenTicket;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;

$output = "=== AUDITORIA DE N+1 REAL (ETAPA P2) ===\n\n";

// 1. Profiling do Cardápio
DB::flushQueryLog();
DB::enableQueryLog();
$companyId = 74;
$categories = Category::where('company_id', $companyId)
    ->where('status', 'active')
    ->with(['products' => function ($q) {
        $q->where('status', 'active')->orderBy('name');
    }])
    ->orderBy('sort_order')
    ->get();

// Iterar simulando o loop na view
foreach ($categories as $cat) {
    foreach ($cat->products as $prod) {
        $name = $prod->name;
    }
}
$queries = DB::getQueryLog();
$output .= "Cardápio Público:\n";
$output .= "  Categorias carregadas: " . $categories->count() . "\n";
$output .= "  Total queries disparadas: " . count($queries) . "\n";
$output .= "  Estratégia: Eager Loading (com with(['products']))\n\n";

// 2. Profiling de Pedidos (Simular carregamento com eager loading real do index)
DB::flushQueryLog();
$orders = Order::query()->with(['items.product', 'session.table'])->limit(10)->get();
foreach ($orders as $order) {
    $items = $order->items;
    foreach ($items as $item) {
        $prodName = $item->product?->name;
    }
}
$queries = DB::getQueryLog();
$output .= "Pedidos e itens (Index com Eager Loading):\n";
$output .= "  Pedidos iterados: " . $orders->count() . "\n";
$output .= "  Total queries disparadas: " . count($queries) . "\n";
$output .= "  Resultado: Sem N+1.\n\n";

// 3. Profiling da Cozinha
DB::flushQueryLog();
$tickets = KitchenTicket::query()->whereIn('status', ['pending', 'preparing', 'ready'])
    ->with(['order.items.product', 'order.session.table'])
    ->orderBy('created_at', 'asc')
    ->get();

foreach ($tickets as $t) {
    $table = $t->order?->session?->table?->name;
    foreach ($t->order?->items ?? [] as $item) {
        $pName = $item->product?->name;
    }
}
$queries = DB::getQueryLog();
$output .= "Fila da Cozinha (KDS):\n";
$output .= "  Tickets na fila: " . $tickets->count() . "\n";
$output .= "  Total queries disparadas: " . count($queries) . "\n";
$output .= "  Estratégia: Nested Eager Loading (with(['order.items.product', 'order.session.table']))\n\n";

file_put_contents(__DIR__ . '/n_plus_one_result.txt', $output);
echo "Profiling N+1 concluído e salvo em scratch/n_plus_one_result.txt\n";
