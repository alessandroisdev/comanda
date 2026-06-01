<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Endpoints de Health e Integridade do Sistema
use App\Http\Controllers\HealthCheckController;

Route::get('/health', [HealthCheckController::class, 'check'])->name('health');
Route::get('/liveness', [HealthCheckController::class, 'liveness'])->name('liveness');
Route::get('/readiness', [HealthCheckController::class, 'readiness'])->name('readiness');

// Importação das rotas de Realtime Server-Sent Events (SSE)
require base_path('routes/sse.php');

// Controllers Administrativos da Fase 2
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

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
});

