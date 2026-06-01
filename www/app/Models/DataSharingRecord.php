<?php

declare(strict_types=1);

namespace App\Models;

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
    protected $fillable = [
        'uuid',
        'company_id',
        'recipient_name',
        'sharing_purpose',
        'legal_basis',
        'shared_data',
        'security_measures',
    ];

    protected static function booted(): void
    {
        static::creating(function (DataSharingRecord $model) {
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
