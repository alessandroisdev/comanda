<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
 * @property string $name
 * @property string|null $description
 * @property CategoryStatusEnum $status
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company $company
 * @property-read Collection|Product[] $products
 */
class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'name',
        'description',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => CategoryStatusEnum::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * Geração automática de UUID na criação da categoria.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Retorna a empresa proprietária da categoria.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Retorna os produtos pertencentes a esta categoria.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
