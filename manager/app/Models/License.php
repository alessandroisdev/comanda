<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $client_name
 * @property string $client_email
 * @property string $client_document
 * @property string $plan_name
 * @property string $type
 * @property string $status
 * @property string|null $key_data
 * @property Carbon|null $issued_at
 * @property Carbon|null $expires_at
 */
class License extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'client_name',
        'client_email',
        'client_document',
        'plan_name',
        'type',
        'status',
        'key_data',
        'issued_at',
        'expires_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'license_modules');
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LicenseAuditLog::class);
    }
}
