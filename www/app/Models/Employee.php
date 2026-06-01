<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $company_id
 * @property int|null $unit_id
 * @property string|null $employee_number
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string|null $document
 * @property Carbon|null $birth_date
 * @property Carbon|null $hire_date
 * @property EmployeeStatusEnum $status
 * @property EmployeeRoleEnum $role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company|null $company
 * @property-read CompanyUnit|null $unit
 * @property-read Collection|Role[] $roles
 */
class Employee extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'unit_id',
        'employee_number',
        'name',
        'email',
        'password',
        'phone',
        'document',
        'birth_date',
        'hire_date',
        'status',
        'role',
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
            'hire_date' => 'date',
            'status' => EmployeeStatusEnum::class,
            'role' => EmployeeRoleEnum::class,
        ];
    }

    /**
     * Geração automática de UUID na criação do funcionário.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Retorna a empresa proprietária do funcionário.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Retorna a unidade física onde o funcionário está alocado.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(CompanyUnit::class);
    }

    /**
     * Retorna os perfis (roles) do funcionário.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'employee_role');
    }
}
