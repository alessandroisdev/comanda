<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TableStatusEnum;
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
 * @property string $code
 * @property string $name
 * @property int $capacity
 * @property string $sector
 * @property TableStatusEnum $status
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Table where(string $column, mixed $value)
 */
class Table extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'code',
        'name',
        'capacity',
        'sector',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatusEnum::class,
            'capacity' => 'integer',
            'sort_order' => 'integer',
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

    public function sessions(): HasMany
    {
        return $this->hasMany(OrderSession::class, 'table_id');
    }
}
