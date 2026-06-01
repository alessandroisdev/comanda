<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PrintJobStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\PrintJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrintJobFactory extends Factory
{
    protected $model = PrintJob::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'unit_id' => CompanyUnit::factory(),
            'type' => 'kitchen_ticket',
            'payload' => ['text' => 'Mesa 5 - Coca-Cola x2'],
            'status' => PrintJobStatusEnum::PENDING,
            'attempts' => 0,
        ];
    }
}
