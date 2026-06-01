<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property int $company_id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $subject_uuid
 * @property string $purpose
 * @property string $consent_text
 * @property string $term_version
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $status
 * @property Carbon|null $revoked_at
 */
class Consent extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'subject_type',
        'subject_id',
        'subject_uuid',
        'purpose',
        'consent_text',
        'term_version',
        'ip_address',
        'user_agent',
        'status',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Consent $model) {
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
