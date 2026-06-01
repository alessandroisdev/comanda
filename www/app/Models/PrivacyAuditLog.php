<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property int $company_id
 * @property int|null $actor_id
 * @property string|null $actor_type
 * @property string $entity_type
 * @property string $entity_uuid
 * @property string $action
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $correlation_id
 */
class PrivacyAuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'actor_id',
        'actor_type',
        'entity_type',
        'entity_uuid',
        'action',
        'ip_address',
        'user_agent',
        'correlation_id',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
