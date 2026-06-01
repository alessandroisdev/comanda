<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UnitStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyUnitFactory extends Factory
{
    protected $model = CompanyUnit::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'company_id' => Company::factory(),
            'status' => UnitStatusEnum::ACTIVE,
            'name' => $this->faker->company() . ' - Filial ' . mt_rand(1, 9),
            'document_number' => sprintf('%014d', mt_rand(10000000000000, 99999999999999)),
            'email' => $this->faker->safeEmail(),
            'phone' => '119' . mt_rand(10000000, 99999999),
            'zipcode' => '01311000',
            'street' => 'Avenida Paulista',
            'number' => (string) mt_rand(100, 3000),
            'district' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'country' => 'Brasil',
            'settings_json' => ['has_delivery' => true],
        ];
    }
}
