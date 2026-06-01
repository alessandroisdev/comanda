<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PrintJobStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property int $company_id
 * @property int $unit_id
 * @property string $type
 * @property array $payload
 * @property PrintJobStatusEnum $status
 * @property int $attempts
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PrintJob where(string $column, mixed $value)
 */
class PrintJob extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'type',
        'payload',
        'status',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'status' => PrintJobStatusEnum::class,
            'payload' => 'array',
            'attempts' => 'integer',
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CompanyUnit::class, 'unit_id');
    }
}
