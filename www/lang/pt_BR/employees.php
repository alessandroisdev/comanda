<?php

declare(strict_types=1);

return [
    'title' => 'Funcionários',
    'title_singular' => 'Funcionário',
    'create' => 'Cadastrar Funcionário',
    'edit' => 'Editar Funcionário',
    'show' => 'Detalhes do Funcionário',
    'list' => 'Listagem de Funcionários',

    'fields' => [
        'uuid' => 'UUID Público',
        'company' => 'Empresa',
        'unit' => 'Unidade Física',
        'employee_number' => 'Matrícula',
        'name' => 'Nome Completo',
        'email' => 'E-mail Comercial',
        'password' => 'Senha de Acesso',
        'phone' => 'Telefone/WhatsApp',
        'document' => 'CPF',
        'birth_date' => 'Data de Nascimento',
        'hire_date' => 'Data de Admissão',
        'role' => 'Cargo/Função',
        'status' => 'Status Administrativo',
        'created_at' => 'Data de Cadastro',
    ],

    'roles' => [
        'waiter' => 'Garçom',
        'cashier' => 'Operador de Caixa',
        'kitchen' => 'Auxiliar de Cozinha',
        'manager' => 'Gerente de Unidade',
        'admin' => 'Administrador de Operações',
        'driver' => 'Entregador/Delivery',
    ],

    'status' => [
        'active' => 'Ativo',
        'suspended' => 'Suspenso',
    ],

    'messages' => [
        'create_success' => 'Funcionário cadastrado com sucesso!',
        'update_success' => 'Funcionário atualizado com sucesso!',
        'delete_success' => 'Funcionário excluído do sistema!',
    ]
];
