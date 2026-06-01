<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'unit_id' => CompanyUnit::factory(),
            'session_id' => OrderSession::factory(),
            'employee_id' => Employee::factory(),
            'order_number' => 'PED-'.$this->faker->unique()->numberBetween(1000, 9999),
            'status' => OrderStatusEnum::DRAFT,
            'subtotal_cents' => 0,
            'discount_cents' => 0,
            'total_cents' => 0,
            'notes' => $this->faker->sentence(),
        ];
    }
}
