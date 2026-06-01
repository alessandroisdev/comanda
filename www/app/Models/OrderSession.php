<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderSessionStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property int $company_id
 * @property int $unit_id
 * @property int|null $table_id
 * @property int $opened_by_employee_id
 * @property int|null $closed_by_employee_id
 * @property OrderSessionStatusEnum $status
 * @property Carbon $opened_at
 * @property Carbon|null $closed_at
 * @property int $people_count
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\CompanyUnit $unit
 * @property-read \App\Models\Table|null $table
 * @property-read \App\Models\Employee $openedBy
 * @property-read \App\Models\Employee|null $closedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderSession where(string $column, mixed $value)
 */
class OrderSession extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'orders_sessions';

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'table_id',
        'opened_by_employee_id',
        'closed_by_employee_id',
        'status',
        'opened_at',
        'closed_at',
        'people_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderSessionStatusEnum::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'people_count' => 'integer',
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

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'opened_by_employee_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'closed_by_employee_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'session_id');
    }
}
