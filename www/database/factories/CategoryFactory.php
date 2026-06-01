<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

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
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'status' => CategoryStatusEnum::ACTIVE,
            'sort_order' => $this->faker->numberBetween(0, 50),
        ];
    }

    /**
     * Define status inativo.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => CategoryStatusEnum::INACTIVE,
        ]);
    }
}
