<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            return;
        }

        $categories = [
            ['name' => 'Entradas', 'description' => 'Petiscos e entradas frias ou quentes para abrir o apetite', 'sort_order' => 1],
            ['name' => 'Pratos Principais', 'description' => 'Nossas deliciosas opções para sua refeição principal', 'sort_order' => 2],
            ['name' => 'Bebidas', 'description' => 'Bebidas com e sem álcool, sucos e refrigerantes', 'sort_order' => 3],
            ['name' => 'Sobremesas', 'description' => 'Delícias doces para encerrar com chave de ouro', 'sort_order' => 4],
        ];

        foreach ($companies as $company) {
            foreach ($categories as $cat) {
                Category::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'sort_order' => $cat['sort_order'],
                    'status' => CategoryStatusEnum::ACTIVE,
                ]);
            }
        }
    }
}
