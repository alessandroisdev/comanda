<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property int $company_id
 * @property int $unit_id
 * @property int $session_id
 * @property int $employee_id
 * @property string $order_number
 * @property OrderStatusEnum $status
 * @property int $subtotal_cents
 * @property int $discount_cents
 * @property int $total_cents
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\CompanyUnit $unit
 * @property-read \App\Models\OrderSession $session
 * @property-read \App\Models\Employee $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read \App\Models\KitchenTicket|null $kitchenTicket
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order where(string $column, mixed $value)
 */
class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'session_id',
        'employee_id',
        'order_number',
        'status',
        'subtotal_cents',
        'discount_cents',
        'total_cents',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'total_cents' => 'integer',
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

    public function session(): BelongsTo
    {
        return $this->belongsTo(OrderSession::class, 'session_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function kitchenTicket(): HasOne
    {
        return $this->hasOne(KitchenTicket::class, 'order_id');
    }
}
