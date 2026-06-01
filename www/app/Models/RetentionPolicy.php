<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 * @property string $data_category
 * @property int $retention_months
 * @property string|null $legal_obligation
 * @property string $disposal_method
 * @method static RetentionPolicy updateOrCreate(array $attributes, array $values = [])
 */
class RetentionPolicy extends Model
{
    protected $fillable = [
        'uuid',
        'data_category',
        'retention_months',
        'legal_obligation',
        'disposal_method',
    ];

    protected static function booted(): void
    {
        static::creating(function (RetentionPolicy $model) {
            if (empty($model->uuid)) {
                $model->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
                    mt_rand(0, 0xFFFF),
                    mt_rand(0, 0x0FFF) | 0x4000,
                    mt_rand(0, 0x3FFF) | 0x8000,
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
                );
            }
        });
    }
}
