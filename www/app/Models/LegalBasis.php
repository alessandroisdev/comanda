<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $law_article
 */
class LegalBasis extends Model
{
    use HasUuids;

    protected $table = 'legal_bases';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'law_article',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
