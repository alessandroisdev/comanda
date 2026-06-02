<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $hostname
 * @property string|null $domain
 * @property string $ip_address
 * @property string $fingerprint
 * @property string $status
 */
class LicenseInstallation extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'hostname',
        'domain',
        'ip_address',
        'fingerprint',
        'status',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
