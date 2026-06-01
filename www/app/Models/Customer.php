<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'phone',
        'document',
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
        ];
    }

    /**
     * Observer simples de criação para gerar o UUID automático.
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
}
