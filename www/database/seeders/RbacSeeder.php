<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Catálogo de Permissões de Governança
        $permissionsCatalog = [
            // Empresas
            'companies.view' => 'Visualizar dados da empresa',
            'companies.create' => 'Cadastrar novas empresas no sistema',
            'companies.update' => 'Editar dados da empresa',
            'companies.delete' => 'Excluir empresas do sistema',

            // Unidades
            'units.view' => 'Visualizar filiais/unidades',
            'units.create' => 'Cadastrar novas filiais/unidades',
            'units.update' => 'Editar filiais/unidades',
            'units.delete' => 'Excluir filiais/unidades',

            // Usuários Administrativos (users)
            'users.view' => 'Visualizar usuários de painel',
            'users.create' => 'Cadastrar novos usuários de painel',
            'users.update' => 'Editar usuários de painel',
            'users.delete' => 'Excluir usuários de painel',

            // Funcionários (employees)
            'employees.view' => 'Visualizar equipe/funcionários',
            'employees.create' => 'Cadastrar novos funcionários',
            'employees.update' => 'Editar funcionários',
            'employees.delete' => 'Excluir funcionários',

            // Clientes (customers)
            'customers.view' => 'Visualizar carteira de clientes',
            'customers.create' => 'Cadastrar novos clientes',
            'customers.update' => 'Editar clientes',
            'customers.delete' => 'Excluir clientes',

            // Módulos
            'modules.view' => 'Visualizar módulos ativos e licenciamento',

            // Categorias
            'categories.view' => 'Visualizar categorias de cardápio',
            'categories.create' => 'Cadastrar categorias de cardápio',
            'categories.update' => 'Editar categorias de cardápio',
            'categories.delete' => 'Excluir categorias de cardápio',

            // Produtos
            'products.view' => 'Visualizar produtos do catálogo',
            'products.create' => 'Cadastrar novos produtos',
            'products.update' => 'Editar produtos',
            'products.delete' => 'Excluir produtos',
        ];

        $permissionModels = [];
        foreach ($permissionsCatalog as $slug => $description) {
            $permissionModels[$slug] = Permission::updateOrCreate(
                ['slug' => $slug],
                [
                    'uuid' => (string) Str::uuid(),
                    'description' => $description,
                ]
            );
        }

        // 2. Perfis de Acesso (Roles)
        $rolesCatalog = [
            'super_admin' => 'Administrador Geral / Dono do Negócio',
            'manager' => 'Gerente de Unidade / Operações',
            'cashier' => 'Operador de Caixa',
            'waiter' => 'Garçom / Atendente',
            'kitchen' => 'Auxiliar de Cozinha / Chef',
        ];

        $roleModels = [];
        foreach ($rolesCatalog as $name => $description) {
            $roleModels[$name] = Role::updateOrCreate(
                ['name' => $name],
                [
                    'uuid' => (string) Str::uuid(),
                    'description' => $description,
                ]
            );
        }

        // 3. Vincular Permissões às Roles

        // Super Admin: Tudo
        $roleModels['super_admin']->permissions()->sync(array_values(array_map(fn ($model) => $model->id, $permissionModels)));

        // Manager: Gerenciamento local da unidade
        $managerPermissions = [
            'units.view',
            'units.update',
            'employees.view',
            'employees.create',
            'employees.update',
            'customers.view',
            'customers.create',
            'customers.update',
            'modules.view',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
        ];
        $managerPermissionIds = array_map(fn ($slug) => $permissionModels[$slug]->id, $managerPermissions);
        $roleModels['manager']->permissions()->sync($managerPermissionIds);

        // Caixa: Clientes e Produtos (para visualização no PDV)
        $cashierPermissions = [
            'customers.view',
            'customers.create',
            'customers.update',
            'products.view',
        ];
        $cashierPermissionIds = array_map(fn ($slug) => $permissionModels[$slug]->id, $cashierPermissions);
        $roleModels['cashier']->permissions()->sync($cashierPermissionIds);

        // Garçom: Apenas ler produtos (cardápio)
        $waiterPermissions = [
            'products.view',
        ];
        $waiterPermissionIds = array_map(fn ($slug) => $permissionModels[$slug]->id, $waiterPermissions);
        $roleModels['waiter']->permissions()->sync($waiterPermissionIds);

        // Cozinha: Ler produtos
        $kitchenPermissions = [
            'products.view',
        ];
        $kitchenPermissionIds = array_map(fn ($slug) => $permissionModels[$slug]->id, $kitchenPermissions);
        $roleModels['kitchen']->permissions()->sync($kitchenPermissionIds);
    }
}
