<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property array|null $dependencies
 * @property string $version_min
 * @property int $price_suggested_cents
 */
class Module extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'status',
        'dependencies',
        'version_min',
        'price_suggested_cents',
    ];

    protected $casts = [
        'dependencies' => 'array',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'license_modules');
    }
}
