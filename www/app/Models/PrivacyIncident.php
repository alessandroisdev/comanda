<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property int $company_id
 * @property string $incident_type
 * @property string $severity
 * @property string $affected_data
 * @property int $affected_subjects_count
 * @property string $description
 * @property string|null $measures_taken
 * @property bool $anpd_notified
 * @property bool $subjects_notified
 * @property string $status
 */
class PrivacyIncident extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'incident_type',
        'severity',
        'affected_data',
        'affected_subjects_count',
        'description',
        'measures_taken',
        'anpd_notified',
        'subjects_notified',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'anpd_notified' => 'boolean',
            'subjects_notified' => 'boolean',
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
