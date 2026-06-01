<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'status' => CompanyStatusEnum::ACTIVE,
            'legal_name' => $this->faker->company().' LTDA',
            'trade_name' => $this->faker->company(),
            'document_type' => DocumentTypeEnum::CNPJ,
            'document_number' => sprintf('%014d', mt_rand(10000000000000, 99999999999999)),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '119'.mt_rand(10000000, 99999999),
            'timezone' => 'America/Sao_Paulo',
            'currency' => 'BRL',
            'language' => 'pt_BR',
            'logo' => null,
            'settings_json' => ['theme' => 'dark'],
        ];
    }
}
