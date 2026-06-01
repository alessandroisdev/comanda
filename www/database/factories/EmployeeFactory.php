<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'company_id' => Company::factory(),
            'unit_id' => function (array $attributes) {
                return CompanyUnit::factory()->create(['company_id' => $attributes['company_id']])->id;
            },
            'employee_number' => 'EMP-'.$this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'phone' => '119'.$this->faker->numberBetween(10000000, 99999999),
            'document' => $this->faker->unique()->cpf(false),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status' => EmployeeStatusEnum::ACTIVE,
            'role' => $this->faker->randomElement(EmployeeRoleEnum::cases()),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Define o cargo específico do funcionário.
     */
    public function role(EmployeeRoleEnum $role): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Define status suspenso.
     */
    public function suspended(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatusEnum::SUSPENDED,
        ]);
    }
}
