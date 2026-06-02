<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $filename
 * @property string $path
 * @property string $disk
 * @property string $checksum
 * @property int $size_bytes
 * @property bool $is_encrypted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Backup extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'disk',
        'checksum',
        'size_bytes',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'size_bytes' => 'integer',
    ];
}
