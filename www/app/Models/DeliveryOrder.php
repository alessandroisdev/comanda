<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'order_id',
        'customer_id',
        'delivery_zone_id',
        'recipient_name',
        'recipient_phone',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'delivery_fee',
        'estimated_delivery_time',
        'status',
        'tracking_code',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'float',
            'estimated_delivery_time' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CompanyUnit::class, 'unit_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(DeliveryTracking::class, 'delivery_order_id');
    }
}
