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
