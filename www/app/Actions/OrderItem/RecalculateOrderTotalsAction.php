<?php

declare(strict_types=1);

namespace App\Actions\OrderItem;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RecalculateOrderTotalsAction
{
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order) {

            // Soma todos os itens em centavos
            $subtotal = (int) $order->items()->sum('total_price_cents');

            $discount = (int) $order->discount_cents;
            $total = max(0, $subtotal - $discount);

            $order->update([
                'subtotal_cents' => $subtotal,
                'total_cents' => $total,
            ]);

            return $order->fresh();
        });
    }
}
