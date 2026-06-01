<?php

declare(strict_types=1);

return [
    'title' => 'Clientes',
    'title_singular' => 'Cliente',
    'create' => 'Cadastrar Cliente',
    'edit' => 'Editar Cliente',
    'show' => 'Detalhes do Cliente',
    'list' => 'Listagem de Clientes',

    'fields' => [
        'uuid' => 'UUID Público',
        'company' => 'Empresa de Origem',
        'name' => 'Nome Completo',
        'email' => 'E-mail principal',
        'password' => 'Senha de Acesso',
        'phone' => 'Telefone/WhatsApp',
        'document' => 'CPF/CNPJ',
        'birth_date' => 'Data de Nascimento',
        'marketing_opt_in' => 'Aceita Receber Novidades e Promoções (Marketing)',
        'status' => 'Status Administrativo',
        'created_at' => 'Data de Cadastro',
    ],

    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],

    'messages' => [
        'create_success' => 'Cliente cadastrado com sucesso!',
        'update_success' => 'Cliente atualizado com sucesso!',
        'delete_success' => 'Cliente excluído do sistema!',
    ]
];
