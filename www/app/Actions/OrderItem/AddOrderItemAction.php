<?php

declare(strict_types=1);

namespace App\Actions\OrderItem;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AddOrderItemAction
{
    public function execute(Order $order, Product $product, int $quantity, ?string $notes = null): OrderItem
    {
        return DB::transaction(function () use ($order, $product, $quantity, $notes) {

            /** @var OrderItem|null $item */
            $item = $order->items()->where('product_id', $product->id)->first();

            if ($item) {
                $newQuantity = $item->quantity + $quantity;
                $item->update([
                    'quantity' => $newQuantity,
                    'total_price_cents' => $item->unit_price_cents * $newQuantity,
                    'notes' => $notes ?? $item->notes,
                ]);
            } else {
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price_cents' => $product->price_cents,
                    'total_price_cents' => $product->price_cents * $quantity,
                    'notes' => $notes,
                ]);
            }

            // Recalcula totais do pedido
            $recalculator = app(RecalculateOrderTotalsAction::class);
            $recalculator->execute($order);

            return $item->fresh();
        });
    }
}
