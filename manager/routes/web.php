<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Portal\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', [HealthCheckController::class, 'check'])->name('health');

Route::redirect('/portal', '/portal/dashboard');

Route::prefix('portal')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'dashboard']);

    Route::get('/licenses', [PortalController::class, 'licenses']);
    Route::post('/licenses', [PortalController::class, 'storeLicense']);
    Route::post('/licenses/{id}/renew', [PortalController::class, 'renewLicense']);
    Route::post('/licenses/{id}/suspend', [PortalController::class, 'suspendLicense']);
    Route::post('/licenses/{id}/cancel', [PortalController::class, 'cancelLicense']);

    Route::get('/modules', [PortalController::class, 'modules']);
    Route::post('/modules', [PortalController::class, 'storeModule']);
    Route::post('/modules/{id}/toggle', [PortalController::class, 'toggleModule']);

    Route::get('/installations', [PortalController::class, 'installations']);
    Route::post('/installations/{id}/toggle', [PortalController::class, 'toggleInstallation']);

    Route::get('/audit', [PortalController::class, 'audit']);
});
