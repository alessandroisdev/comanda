<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property int $company_id
 * @property string $recipient_name
 * @property string $sharing_purpose
 * @property string $legal_basis
 * @property string $shared_data
 * @property string|null $security_measures
 */
class DataSharingRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'recipient_name',
        'sharing_purpose',
        'legal_basis',
        'shared_data',
        'security_measures',
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
