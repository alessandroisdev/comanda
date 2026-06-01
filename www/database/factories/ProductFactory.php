<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductStatusEnum;
use App\Models\Company;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 5, 120); // R$ 5,00 a R$ 120,00
        $cost = $price * $this->faker->randomFloat(2, 0.2, 0.5); // Custo de 20% a 50% do preço

        return [
            'uuid' => (string) Str::uuid(),
            'company_id' => Company::factory(),
            'category_id' => function (array $attributes) {
                return Category::factory()->create(['company_id' => $attributes['company_id']])->id;
            },
            'sku' => 'SKU-' . $this->faker->unique()->numberBetween(10000, 99999),
            'barcode' => $this->faker->ean13(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price_cents' => (int) round($price * 100),
            'cost_cents' => (int) round($cost * 100),
            'status' => ProductStatusEnum::ACTIVE,
            'image' => 'https://via.placeholder.com/150',
            'preparation_time' => $this->faker->numberBetween(5, 45), // 5 a 45 min
        ];
    }

    /**
     * Define status inativo.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatusEnum::INACTIVE,
        ]);
    }
}
