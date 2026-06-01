<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductStatusEnum;
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
 * @property int $category_id
 * @property string|null $sku
 * @property string|null $barcode
 * @property string $name
 * @property string|null $description
 * @property int $price_cents
 * @property int|null $cost_cents
 * @property ProductStatusEnum $status
 * @property string|null $image
 * @property int $preparation_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company $company
 * @property-read Category $category
 * @property-read string $formatted_price
 * @property-read string|null $formatted_cost
 */
class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'category_id',
        'sku',
        'barcode',
        'name',
        'description',
        'price_cents',
        'cost_cents',
        'status',
        'image',
        'preparation_time',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'cost_cents' => 'integer',
            'status' => ProductStatusEnum::class,
            'preparation_time' => 'integer',
        ];
    }

    /**
     * Geração automática de UUID na criação do produto.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Retorna a empresa proprietária do produto.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Retorna a categoria a qual o produto pertence.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Helper para formatar o preço em Reais (BRL).
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ '.number_format($this->price_cents / 100, 2, ',', '.');
    }

    /**
     * Helper para formatar o custo em Reais (BRL).
     */
    public function getFormattedCostAttribute(): ?string
    {
        if ($this->cost_cents === null) {
            return null;
        }

        return 'R$ '.number_format($this->cost_cents / 100, 2, ',', '.');
    }
}
