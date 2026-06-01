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
 * @property string $subject_uuid
 * @property string $request_type
 * @property string $status
 * @property Carbon $deadline_at
 * @property string|null $assigned_to
 * @property string|null $response_content
 * @property string|null $evidence_notes
 * @property Carbon|null $completed_at
 */
class DataSubjectRequest extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'subject_type',
        'subject_uuid',
        'request_type',
        'status',
        'deadline_at',
        'assigned_to',
        'response_content',
        'evidence_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DataSubjectRequest $model) {
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
