<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
