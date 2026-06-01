<?php

declare(strict_types=1);

return [
    'title' => 'Categorias',
    'title_singular' => 'Categoria',
    'create' => 'Cadastrar Categoria',
    'edit' => 'Editar Categoria',
    'show' => 'Detalhes da Categoria',
    'list' => 'Listagem de Categorias',

    'fields' => [
        'uuid' => 'UUID Público',
        'company' => 'Empresa Proprietária',
        'name' => 'Nome da Categoria',
        'description' => 'Descrição',
        'status' => 'Status Administrativo',
        'sort_order' => 'Ordem de Exibição',
        'created_at' => 'Data de Cadastro',
    ],

    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],

    'messages' => [
        'create_success' => 'Categoria cadastrada com sucesso!',
        'update_success' => 'Categoria atualizada com sucesso!',
        'delete_success' => 'Categoria excluída com sucesso!',
    ],
];
