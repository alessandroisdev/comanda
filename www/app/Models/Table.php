<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TableStatusEnum;
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
 * @property string|null $public_uuid
 * @property int $company_id
 * @property int $unit_id
 * @property string $code
 * @property string $name
 * @property string|null $slug
 * @property int $capacity
 * @property string $sector
 * @property TableStatusEnum $status
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Table where(string $column, mixed $value)
 */
class Table extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'public_uuid',
        'company_id',
        'unit_id',
        'code',
        'name',
        'slug',
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
        return ['uuid', 'public_uuid'];
    }

    protected static function booted(): void
    {
        static::creating(function (Table $table) {
            if (empty($table->slug)) {
                $table->slug = \Illuminate\Support\Str::slug($table->name . '-' . $table->code . '-' . \Illuminate\Support\Str::random(5));
            }
        });
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
