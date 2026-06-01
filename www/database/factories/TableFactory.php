<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TableStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'unit_id' => CompanyUnit::factory(),
            'code' => 'M-' . $this->faker->unique()->numberBetween(1, 999),
            'name' => 'Mesa ' . $this->faker->numberBetween(1, 100),
            'capacity' => $this->faker->randomElement([2, 4, 6, 8]),
            'sector' => $this->faker->randomElement(['Salão Principal', 'Varanda', 'Área VIP']),
            'status' => TableStatusEnum::AVAILABLE,
            'sort_order' => 0,
        ];
    }
}
