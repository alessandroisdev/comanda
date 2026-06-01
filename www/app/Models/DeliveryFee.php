<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFee extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'delivery_zone_id',
        'price',
        'estimated_minutes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'estimated_minutes' => 'integer',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }
}
