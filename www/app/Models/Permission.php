<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $slug
 * @property string|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Role[] $roles
 */
class Permission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'slug',
        'description',
    ];

    /**
     * Geração automática de UUID na criação.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Relação com as roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
