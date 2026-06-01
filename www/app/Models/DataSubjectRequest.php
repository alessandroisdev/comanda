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
    use HasUuids;

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

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
