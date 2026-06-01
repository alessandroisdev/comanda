<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderSessionStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\OrderSession;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderSessionFactory extends Factory
{
    protected $model = OrderSession::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'unit_id' => CompanyUnit::factory(),
            'table_id' => Table::factory(),
            'opened_by_employee_id' => Employee::factory(),
            'closed_by_employee_id' => null,
            'status' => OrderSessionStatusEnum::OPEN,
            'opened_at' => Carbon::now(),
            'closed_at' => null,
            'people_count' => $this->faker->numberBetween(1, 6),
            'notes' => $this->faker->sentence(),
        ];
    }
}
