<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerStatusEnum;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'phone' => '119'.$this->faker->numberBetween(10000000, 99999999),
            'document' => $this->faker->unique()->cpf(false),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-15 years')->format('Y-m-d'),
            'marketing_opt_in' => $this->faker->boolean(40), // 40% de chance de aceitar marketing
            'status' => CustomerStatusEnum::ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Define status inativo.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatusEnum::INACTIVE,
        ]);
    }
}
