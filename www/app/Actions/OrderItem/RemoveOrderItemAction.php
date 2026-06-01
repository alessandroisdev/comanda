<?php

declare(strict_types=1);

namespace App\Actions\OrderItem;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class RemoveOrderItemAction
{
    public function execute(OrderItem $item): Order
    {
        return DB::transaction(function () use ($item) {
            $order = $item->order;

            $item->delete();

            // Recalcula totais do pedido
            $recalculator = app(RecalculateOrderTotalsAction::class);

            return $recalculator->execute($order);
        });
    }
}
