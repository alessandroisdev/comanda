<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $license_id
 * @property string|null $installation_uuid
 * @property string $action
 * @property array|null $details
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $created_at
 */
class LicenseAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'license_id',
        'installation_uuid',
        'action',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
