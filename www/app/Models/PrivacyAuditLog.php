<?php

declare(strict_types=1);

namespace App\Models;

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

    protected static function booted(): void
    {
        static::creating(function (PrivacyAuditLog $model) {
            if (empty($model->uuid)) {
                $model->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
                    mt_rand(0, 0xFFFF),
                    mt_rand(0, 0x0FFF) | 0x4000,
                    mt_rand(0, 0x3FFF) | 0x8000,
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
                );
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
