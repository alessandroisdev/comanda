<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property CompanyStatusEnum $status
 * @property string $legal_name
 * @property string $trade_name
 * @property DocumentTypeEnum $document_type
 * @property string $document_number
 * @property string $email
 * @property string $phone
 * @property string $timezone
 * @property string $currency
 * @property string $language
 * @property string|null $logo
 * @property array|null $settings_json
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Company extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'uuid',
        'status',
        'legal_name',
        'trade_name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'timezone',
        'currency',
        'language',
        'logo',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompanyStatusEnum::class,
            'document_type' => DocumentTypeEnum::class,
            'settings_json' => 'array',
        ];
    }

    /**
     * Boot do model para injeção e geração automática de UUID na criação.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Relação com as unidades físicas.
     */
    public function units(): HasMany
    {
        return $this->hasMany(CompanyUnit::class);
    }
}
