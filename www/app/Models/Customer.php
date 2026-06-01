<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $company_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string|null $document
 * @property Carbon|null $birth_date
 * @property bool $marketing_opt_in
 * @property CustomerStatusEnum $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company|null $company
 */
class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'document',
        'birth_date',
        'marketing_opt_in',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'marketing_opt_in' => 'boolean',
            'status' => CustomerStatusEnum::class,
        ];
    }

    /**
     * Geração automática de UUID na criação do cliente.
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->uuid)) {
                $customer->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
                    mt_rand(0, 0xFFFF),
                    mt_rand(0, 0x0FFF) | 0x4000,
                    mt_rand(0, 0x3FFF) | 0x8000,
                    mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
                );
            }
        });
    }

    /**
     * Retorna a empresa onde o cliente se cadastrou originalmente.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
