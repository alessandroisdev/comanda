<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTracking extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'delivery_trackings';

    protected $fillable = [
        'uuid',
        'delivery_order_id',
        'latitude',
        'longitude',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }
}
