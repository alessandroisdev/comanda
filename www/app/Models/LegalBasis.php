<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $law_article
 */
class LegalBasis extends Model
{
    protected $table = 'legal_bases';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'law_article',
    ];

    protected static function booted(): void
    {
        static::creating(function (LegalBasis $model) {
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
}
