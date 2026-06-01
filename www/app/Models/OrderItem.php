<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property int $unit_price_cents
 * @property int $total_price_cents
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem where(string $column, mixed $value)
 */
class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'uuid',
        'order_id',
        'product_id',
        'quantity',
        'unit_price_cents',
        'total_price_cents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
            'total_price_cents' => 'integer',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
