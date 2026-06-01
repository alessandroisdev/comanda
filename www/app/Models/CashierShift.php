<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashierShiftStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property int $company_id
 * @property int $unit_id
 * @property int $opened_by
 * @property int|null $closed_by
 * @property \Carbon\Carbon $opened_at
 * @property \Carbon\Carbon|null $closed_at
 * @property int $opening_amount_cents
 * @property int|null $closing_amount_cents
 * @property CashierShiftStatusEnum $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CashierShift where(string $column, mixed $value)
 */
class CashierShift extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'cashier_shifts';

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'opening_amount_cents',
        'closing_amount_cents',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CashierShiftStatusEnum::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_amount_cents' => 'integer',
            'closing_amount_cents' => 'integer',
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

    public function openedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'opened_by');
    }

    public function closedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'closed_by');
    }
}
