<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitStatusEnum;
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
 * @property UnitStatusEnum $status
 * @property string $name
 * @property string|null $document_number
 * @property string|null $email
 * @property string|null $phone
 * @property string $zipcode
 * @property string $street
 * @property string $number
 * @property string $district
 * @property string $city
 * @property string $state
 * @property string $country
 * @property array|null $settings_json
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company $company
 */
class CompanyUnit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'status',
        'name',
        'document_number',
        'email',
        'phone',
        'zipcode',
        'street',
        'number',
        'district',
        'city',
        'state',
        'country',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'status' => UnitStatusEnum::class,
            'settings_json' => 'array',
        ];
    }

    /**
     * Geração automática de UUID na criação da unidade.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Retorna a empresa proprietária da unidade.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
