<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property int $company_id
 * @property string $code
 * @property string $type
 * @property int $value
 * @property int $min_order_amount_cents
 * @property int|null $max_discount_cents
 * @property Carbon|null $expires_at
 * @property int|null $usage_limit
 * @property int $used_count
 * @property boolean $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company $company
 */
class Coupon extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'code',
        'type',
        'value',
        'min_order_amount_cents',
        'max_discount_cents',
        'expires_at',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_order_amount_cents' => 'integer',
            'max_discount_cents' => 'integer',
            'expires_at' => 'datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
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

    /**
     * Calcula o desconto aplicável com base no subtotal de centavos.
     */
    public function calculateDiscount(int $subtotalCents): int
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 0;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 0;
        }

        if ($subtotalCents < $this->min_order_amount_cents) {
            return 0;
        }

        if ($this->type === 'fixed') {
            $discount = $this->value;
        } else {
            // Percentagem
            $discount = (int) round($subtotalCents * ($this->value / 100));
        }

        if ($this->max_discount_cents !== null && $discount > $this->max_discount_cents) {
            $discount = $this->max_discount_cents;
        }

        return min($discount, $subtotalCents);
    }
}
