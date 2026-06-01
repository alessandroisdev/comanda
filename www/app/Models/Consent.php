<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
    use HasUuids;

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

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
