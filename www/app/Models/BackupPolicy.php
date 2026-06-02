<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupPolicy extends Model
{
    protected $fillable = [
        'name',
        'frequency',
        'destination',
        'retention_days',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'retention_days' => 'integer',
    ];
}
