<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'name',
        'type', // cep, bairro, raio
        'zone_data',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'zone_data' => 'array',
            'is_active' => 'boolean',
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

    public function fees(): HasMany
    {
        return $this->hasMany(DeliveryFee::class, 'delivery_zone_id');
    }
}
