<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderSessionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Public\CardapioController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', [HealthCheckController::class, 'check'])->name('health');
Route::get('/liveness', [HealthCheckController::class, 'liveness'])->name('liveness');
Route::get('/readiness', [HealthCheckController::class, 'readiness'])->name('readiness');

// Importação das rotas de Realtime Server-Sent Events (SSE)
require base_path('routes/sse.php');

// Rotas Administrativas em Blade
Route::prefix('admin')->name('admin.')->group(function () {
    // Empresas
    Route::post('companies/datatable', [CompanyController::class, 'datatable'])->name('companies.datatable');
    Route::resource('companies', CompanyController::class)->parameters(['companies' => 'company']);

    // Unidades
    Route::post('units/datatable', [UnitController::class, 'datatable'])->name('units.datatable');
    Route::resource('units', UnitController::class)->parameters(['units' => 'unit']);

    // Usuários Administrativos
    Route::post('users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
    Route::resource('users', UserController::class)->parameters(['users' => 'user']);

    // Funcionários
    Route::post('employees/datatable', [EmployeeController::class, 'datatable'])->name('employees.datatable');
    Route::resource('employees', EmployeeController::class)->parameters(['employees' => 'employee']);

    // Clientes
    Route::post('customers/datatable', [CustomerController::class, 'datatable'])->name('customers.datatable');
    Route::resource('customers', CustomerController::class)->parameters(['customers' => 'customer']);

    // Módulos e Licenciamento
    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');

    // Categorias
    Route::post('categories/datatable', [CategoryController::class, 'datatable'])->name('categories.datatable');
    Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']);

    // Produtos
    Route::post('products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
    Route::resource('products', ProductController::class)->parameters(['products' => 'product']);

    // --- ROTAS DA FASE 3 (OPERACIONAL) ---

    // Mesas
    Route::post('tables/datatable', [TableController::class, 'datatable'])->name('tables.datatable');
    Route::post('tables/{table}/change-status', [TableController::class, 'changeStatus'])->name('tables.change-status');
    Route::resource('tables', TableController::class)->parameters(['tables' => 'table']);

    // Comandas
    Route::post('sessions/datatable', [OrderSessionController::class, 'datatable'])->name('sessions.datatable');
    Route::post('sessions/{session}/close', [OrderSessionController::class, 'close'])->name('sessions.close');
    Route::post('sessions/{session}/cancel', [OrderSessionController::class, 'cancel'])->name('sessions.cancel');
    Route::post('sessions/{session}/transfer', [OrderSessionController::class, 'transfer'])->name('sessions.transfer');
    Route::post('sessions/{session}/merge', [OrderSessionController::class, 'merge'])->name('sessions.merge');
    Route::resource('sessions', OrderSessionController::class)->parameters(['sessions' => 'session']);

    // Pedidos e Itens
    Route::post('orders/{order}/send-to-kitchen', [OrderController::class, 'sendToKitchen'])->name('orders.send-to-kitchen');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.add');
    Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.remove');
    Route::patch('orders/{order}/items/{item}/quantity', [OrderController::class, 'updateItemQuantity'])->name('orders.items.update-quantity');
    Route::resource('orders', OrderController::class)->only(['store', 'show'])->parameters(['orders' => 'order']);

    // Cozinha / Produção
    Route::get('kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::post('kitchen/{ticket}/start', [KitchenController::class, 'start'])->name('kitchen.start');
    Route::post('kitchen/{ticket}/ready', [KitchenController::class, 'ready'])->name('kitchen.ready');
    Route::post('kitchen/{ticket}/complete', [KitchenController::class, 'complete'])->name('kitchen.complete');
    Route::post('kitchen/{ticket}/cancel', [KitchenController::class, 'cancel'])->name('kitchen.cancel');

    // Caixa Operacional
    Route::get('cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('cashier', [CashierController::class, 'store'])->name('cashier.store');
    Route::get('cashier/{shift}', [CashierController::class, 'show'])->name('cashier.show');
    Route::post('cashier/{shift}/close', [CashierController::class, 'close'])->name('cashier.close');
});

// Rotas API para Deleção via AJAX nos DataTables
Route::prefix('api/v1')->group(function () {
    Route::delete('companies/{company}', [CompanyController::class, 'destroy']);
    Route::delete('units/{unit}', [UnitController::class, 'destroy']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    // API da Fase 3
    Route::delete('tables/{table}', [TableController::class, 'destroy']);
});

// Cardápio Digital & Deep Links
Route::get('/cardapio', [CardapioController::class, 'index'])->name('public.menu');
Route::get('/mesa/{slug}', [CardapioController::class, 'index'])->name('public.menu.table');
Route::get('/qrcode/{public_uuid}', [CardapioController::class, 'qrcode'])->name('public.menu.qrcode');

// Tablet, Totem e Delivery views
Route::get('/cardapio/m/{public_uuid}', [CardapioController::class, 'tablet'])->name('public.menu.tablet')->middleware('license.module:tablet_table');
Route::get('/totem', [CardapioController::class, 'totem'])->name('public.menu.totem')->middleware('license.module:kiosk');
Route::get('/delivery', [CardapioController::class, 'delivery'])->name('public.menu.delivery')->middleware('license.module:delivery');
