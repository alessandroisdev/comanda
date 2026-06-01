<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyUnit;
use App\Enums\UnitStatusEnum;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        if ($company && CompanyUnit::count() === 0) {
            CompanyUnit::create([
                'uuid' => 'a3b392a8-129b-43d9-a9a3-a5c7c2512f45',
                'company_id' => $company->id,
                'status' => UnitStatusEnum::ACTIVE,
                'name' => 'Comanda Unidade Paulista',
                'document_number' => '12345678000277',
                'email' => 'paulista@comanda.com.br',
                'phone' => '1132530000',
                'zipcode' => '01311000',
                'street' => 'Avenida Paulista',
                'number' => '1000',
                'district' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'country' => 'Brasil',
                'settings_json' => [
                    'tables_count' => 30,
                    'has_delivery' => true
                ]
            ]);
        }
    }
}
