<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
    use HasUuids;

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

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
