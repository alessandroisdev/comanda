<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryStatusEnum;
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
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection|Product[] $products
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

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
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->uuid)) {
                $category->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
                    mt_rand(0, 0xFFFF),
                    mt_rand(0, 0x0FFF) | 0x4000,
                    mt_rand(0, 0x3FFF) | 0x8000,
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
                );
            }
        });
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
