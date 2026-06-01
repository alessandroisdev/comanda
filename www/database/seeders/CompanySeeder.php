<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar empresa matriz padrão se não existir
        if (Company::count() === 0) {
            Company::create([
                'uuid' => 'f3b392a8-129b-43d9-a9a3-a5c7c2512f45',
                'status' => CompanyStatusEnum::ACTIVE,
                'legal_name' => 'Comanda Tecnologia e Gestão de Restaurantes LTDA',
                'trade_name' => 'Comanda Premium',
                'document_type' => DocumentTypeEnum::CNPJ,
                'document_number' => '12345678000199',
                'email' => 'admin@comanda.com.br',
                'phone' => '11999999999',
                'timezone' => 'America/Sao_Paulo',
                'currency' => 'BRL',
                'language' => 'pt_BR',
                'settings_json' => [
                    'billing_plan' => 'enterprise',
                    'sse_heartbeat_interval' => 15
                ]
            ]);
        }
    }
}
