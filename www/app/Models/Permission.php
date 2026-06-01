<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $slug
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Role[] $roles
 */
class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'slug',
        'description',
    ];

    /**
     * Geração automática de UUID na criação.
     */
    protected static function booted(): void
    {
        static::creating(function (Permission $permission) {
            if (empty($permission->uuid)) {
                $permission->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relação com as roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
