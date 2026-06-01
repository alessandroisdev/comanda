<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 * @property string $data_category
 * @property int $retention_months
 * @property string|null $legal_obligation
 * @property string $disposal_method
 *
 * @method static RetentionPolicy updateOrCreate(array $attributes, array $values = [])
 */
class RetentionPolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'data_category',
        'retention_months',
        'legal_obligation',
        'disposal_method',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
