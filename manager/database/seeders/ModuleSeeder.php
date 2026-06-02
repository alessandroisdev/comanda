<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'code' => 'pdv',
                'name' => 'PDV Operacional',
                'description' => 'Frente de caixa rápido e emissão de cupons de venda.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 9900, // R$ 99,00
            ],
            [
                'code' => 'tables',
                'name' => 'Gestão de Mesas',
                'description' => 'Mapeamento de salão físico, ocupação e controle visual.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 4900,
            ],
            [
                'code' => 'sessions',
                'name' => 'Comandas Eletrônicas',
                'description' => 'Controle individual e por mesa de comandas de consumo.',
                'status' => 'active',
                'dependencies' => ['tables'],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 5900,
            ],
            [
                'code' => 'kitchen',
                'name' => 'KDS (Monitor de Cozinha)',
                'description' => 'Fila de preparo e monitor de produção KDS reativo.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 7900,
            ],
            [
                'code' => 'delivery',
                'name' => 'Site e Gestão de Delivery',
                'description' => 'Menu online para delivery com frete inteligente ViaCEP e cupons.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 14900,
            ],
            [
                'code' => 'kiosk',
                'name' => 'Totem de Autoatendimento',
                'description' => 'Interface dark premium standalone para totens físicos de autoatendimento.',
                'status' => 'active',
                'dependencies' => ['pdv'],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 19900,
            ],
            [
                'code' => 'tablet_table',
                'name' => 'Tablet de Mesa',
                'description' => 'Menu reativo local para tablets fixados nas mesas do salão.',
                'status' => 'active',
                'dependencies' => ['tables', 'sessions'],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 11900,
            ],
            [
                'code' => 'cashier',
                'name' => 'Módulo Financeiro',
                'description' => 'Fechamento de turnos, contas a pagar, receber e conciliação.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 8900,
            ],
            [
                'code' => 'crm',
                'name' => 'CRM Corporativo',
                'description' => 'Base unificada de clientes, histórico de consumo e LGPD.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 6900,
            ],
            [
                'code' => 'loyalty',
                'name' => 'Programa de Fidelidade',
                'description' => 'Cashback, pontos e campanhas promocionais integradas.',
                'status' => 'active',
                'dependencies' => ['crm'],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 7900,
            ],
            [
                'code' => 'whatsapp',
                'name' => 'Notificações WhatsApp',
                'description' => 'Disparo automático de status do pedido e chamados de suporte.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 5900,
            ],
            [
                'code' => 'reports_adv',
                'name' => 'Relatórios Avançados',
                'description' => 'Exportação completa de vendas, insumos e fechamentos.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 6900,
            ],
            [
                'code' => 'bi',
                'name' => 'Módulo BI & Insights',
                'description' => 'Inteligência comercial com painéis e gráficos preditivos.',
                'status' => 'active',
                'dependencies' => ['reports_adv'],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 12900,
            ],
            [
                'code' => 'multi_unit',
                'name' => 'Multiunidade (Multi-Store)',
                'description' => 'Gestão corporativa de várias filiais e franqueados na mesma licença.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 24900,
            ],
            [
                'code' => 'api_external',
                'name' => 'API Externa para Integrações',
                'description' => 'Integrações abertas com iFood, Rappi e ERPs legados.',
                'status' => 'active',
                'dependencies' => [],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 14900,
            ],
            [
                'code' => 'integrations',
                'name' => 'Módulo de Integrações Fiscais',
                'description' => 'Emissão automática de NFC-e, SAT e CF-e.',
                'status' => 'active',
                'dependencies' => ['pdv'],
                'version_min' => '1.0.0',
                'price_suggested_cents' => 11900,
            ],
        ];

        foreach ($modules as $mod) {
            $existing = Module::where('code', $mod['code'])->first();
            if ($existing) {
                $existing->update([
                    'name' => $mod['name'],
                    'description' => $mod['description'],
                    'status' => $mod['status'],
                    'dependencies' => $mod['dependencies'],
                    'version_min' => $mod['version_min'],
                    'price_suggested_cents' => $mod['price_suggested_cents'],
                ]);
            } else {
                Module::create([
                    'uuid' => (string) Str::uuid(),
                    'code' => $mod['code'],
                    'name' => $mod['name'],
                    'description' => $mod['description'],
                    'status' => $mod['status'],
                    'dependencies' => $mod['dependencies'],
                    'version_min' => $mod['version_min'],
                    'price_suggested_cents' => $mod['price_suggested_cents'],
                ]);
            }
        }
    }
}
