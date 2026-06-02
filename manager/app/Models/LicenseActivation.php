<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $license_id
 * @property string $installation_uuid
 * @property string $hostname
 * @property string|null $domain
 * @property string $ip_address
 * @property string $fingerprint
 * @property string $status
 * @property \Carbon\Carbon $activated_at
 * @property \Carbon\Carbon|null $revoked_at
 */
class LicenseActivation extends Model
{
    protected $fillable = [
        'license_id',
        'installation_uuid',
        'hostname',
        'domain',
        'ip_address',
        'fingerprint',
        'status',
        'activated_at',
        'revoked_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
