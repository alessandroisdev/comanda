<?php

use App\Actions\Table\CallWaiterAction;
use App\Actions\Table\RequestBillAction;
use App\Http\Controllers\Public\CardapioController;
use App\Http\Controllers\Public\MenuCategoryController;
use App\Http\Controllers\Public\MenuProductController;
use App\Models\Table;
use Illuminate\Support\Facades\Route;

// APIs públicas para PWA/Tablet/Totem/Delivery
Route::prefix('v1')->group(function () {
    Route::get('menu/categories', [MenuCategoryController::class, 'index']);
    Route::get('menu/products', [MenuProductController::class, 'index']);
    Route::get('menu/products/{uuid}', [MenuProductController::class, 'show']);

    // Chamados e Reações de Mesa reativas via SSE
    Route::post('tables/{tableUuid}/call-waiter', function (string $tableUuid) {
        /** @var Table $table */
        $table = Table::where('public_uuid', $tableUuid)->firstOrFail();

        app(CallWaiterAction::class)->execute($table);

        return response()->json(['success' => true, 'message' => 'Garçom chamado.']);
    });

    Route::post('tables/{tableUuid}/request-bill', function (string $tableUuid) {
        /** @var Table $table */
        $table = Table::where('public_uuid', $tableUuid)->firstOrFail();

        app(RequestBillAction::class)->execute($table);

        return response()->json(['success' => true, 'message' => 'Conta solicitada.']);
    });

    // APIs da Fase 4
    Route::post('tablet/order', [CardapioController::class, 'tabletOrder'])->middleware('license.module:tablet_table');
    Route::post('totem/order', [CardapioController::class, 'checkoutTotem'])->middleware('license.module:kiosk');
    Route::get('coupons/validate', [CardapioController::class, 'validateCoupon']);
    Route::get('delivery/frete', [CardapioController::class, 'calculateFrete'])->middleware('license.module:delivery');
    Route::post('delivery/checkout', [CardapioController::class, 'checkoutDelivery'])->middleware('license.module:delivery');
    Route::post('payments/webhooks/{gateway}', [CardapioController::class, 'webhook']);
});
