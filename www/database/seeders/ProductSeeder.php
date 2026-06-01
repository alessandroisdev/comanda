<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            return;
        }

        // Cardápio de produtos realistas por categoria
        $menuTemplates = [
            'Entradas' => [
                ['name' => 'Batata Rústica da Casa', 'price' => 28.90, 'cost' => 8.50, 'prep' => 12, 'desc' => 'Batatas fritas com casca salpicadas com páprica defumada, alecrim e maionese artesanal de alho'],
                ['name' => 'Bruschetta de Pomodoro', 'price' => 24.50, 'cost' => 6.20, 'prep' => 8, 'desc' => 'Pão italiano tostado, tomates frescos em cubos, manjericão fresco, alho e azeite extravirgem'],
            ],
            'Pratos Principais' => [
                ['name' => 'Filé Mignon ao Molho Madeira', 'price' => 74.90, 'cost' => 28.00, 'prep' => 20, 'desc' => 'Medalhão de filé mignon grelhado regado ao molho madeira, servido com risoto de queijo parmesão'],
                ['name' => 'Fettuccine Carbonara Clássico', 'price' => 54.00, 'cost' => 16.50, 'prep' => 15, 'desc' => 'Massa fettuccine envolta em emulsão de gemas, queijo pecorino romano, panceta crocante e pimenta preta'],
            ],
            'Bebidas' => [
                ['name' => 'Coca-Cola Lata 350ml', 'price' => 6.50, 'cost' => 2.20, 'prep' => 2, 'desc' => 'Refrigerante gelado em lata de 350ml'],
                ['name' => 'Suco de Laranja Natural', 'price' => 9.90, 'cost' => 3.00, 'prep' => 5, 'desc' => 'Suco natural extraído na hora, sem conservantes, garrafa de 400ml'],
                ['name' => 'Cerveja Artesanal IPA 500ml', 'price' => 22.00, 'cost' => 9.50, 'prep' => 2, 'desc' => 'Cerveja tipo India Pale Ale de produção local com notas cítricas e amargor marcante'],
            ],
            'Sobremesas' => [
                ['name' => 'Pudim de Leite Condensado Vovó', 'price' => 14.90, 'cost' => 3.50, 'prep' => 3, 'desc' => 'Fatia generosa de pudim super cremoso com calda clássica de caramelo'],
                ['name' => 'Grand Gateau com Picolé', 'price' => 32.00, 'cost' => 11.20, 'prep' => 10, 'desc' => 'Bolo quente de chocolate derretendo por dentro, servido em ramequin com picolé de baunilha e calda extra de brigadeiro'],
            ],
        ];

        foreach ($categories as $category) {
            $catName = $category->name;
            $templates = $menuTemplates[$catName] ?? [];

            foreach ($templates as $index => $item) {
                Product::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $category->company_id,
                    'category_id' => $category->id,
                    'sku' => 'PROD-'.$category->company_id.$category->id.$index,
                    'barcode' => '7890000'.$category->company_id.$category->id.$index,
                    'name' => $item['name'],
                    'description' => $item['desc'],
                    'price_cents' => (int) round($item['price'] * 100),
                    'cost_cents' => (int) round($item['cost'] * 100),
                    'status' => ProductStatusEnum::ACTIVE,
                    'preparation_time' => $item['prep'],
                ]);
            }
        }
    }
}
