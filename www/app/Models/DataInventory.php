<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 * @property string $data_name
 * @property string $data_category
 * @property string $processing_purpose
 * @property string $legal_basis
 * @property string $data_subject_type
 * @property string|null $table_name
 * @property string|null $column_name
 * @property string|null $retention_period
 * @property string|null $security_measures
 */
class DataInventory extends Model
{
    protected $fillable = [
        'uuid',
        'data_name',
        'data_category',
        'processing_purpose',
        'legal_basis',
        'data_subject_type',
        'table_name',
        'column_name',
        'retention_period',
        'security_measures',
    ];

    protected static function booted(): void
    {
        static::creating(function (DataInventory $model) {
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
