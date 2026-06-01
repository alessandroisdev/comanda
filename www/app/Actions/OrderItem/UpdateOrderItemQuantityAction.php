<?php

declare(strict_types=1);

namespace App\Actions\OrderItem;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class UpdateOrderItemQuantityAction
{
    public function execute(OrderItem $item, int $quantity): OrderItem
    {
        return DB::transaction(function () use ($item, $quantity) {
            $item->update([
                'quantity' => $quantity,
                'total_price_cents' => $item->unit_price_cents * $quantity,
            ]);

            // Recalcula totais do pedido
            $recalculator = app(RecalculateOrderTotalsAction::class);
            $recalculator->execute($item->order);

            return $item->fresh();
        });
    }
}
