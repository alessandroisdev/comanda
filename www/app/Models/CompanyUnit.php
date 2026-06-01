<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitStatusEnum;
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
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Company $company
 */
class CompanyUnit extends Model
{
    use HasFactory, SoftDeletes;

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
    protected static function booted(): void
    {
        static::creating(function (CompanyUnit $unit) {
            if (empty($unit->uuid)) {
                $unit->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
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
     * Retorna a empresa proprietária da unidade.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
