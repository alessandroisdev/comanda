<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KitchenTicketStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property int $order_id
 * @property KitchenTicketStatusEnum $status
 * @property \Carbon\Carbon $sent_at
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $ready_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|KitchenTicket where(string $column, mixed $value)
 */
class KitchenTicket extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kitchen_tickets';

    protected $fillable = [
        'uuid',
        'order_id',
        'status',
        'sent_at',
        'started_at',
        'ready_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => KitchenTicketStatusEnum::class,
            'sent_at' => 'datetime',
            'started_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
