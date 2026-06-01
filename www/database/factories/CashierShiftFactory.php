<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CashierShiftStatusEnum;
use App\Models\CashierShift;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CashierShiftFactory extends Factory
{
    protected $model = CashierShift::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'unit_id' => CompanyUnit::factory(),
            'opened_by' => Employee::factory(),
            'closed_by' => null,
            'opened_at' => Carbon::now(),
            'closed_at' => null,
            'opening_amount_cents' => $this->faker->numberBetween(10000, 50000), // R$ 100,00 a R$ 500,00
            'closing_amount_cents' => null,
            'status' => CashierShiftStatusEnum::OPEN,
        ];
    }
}
