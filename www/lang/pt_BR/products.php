<?php

declare(strict_types=1);

return [
    'title' => 'Produtos',
    'title_singular' => 'Produto',
    'create' => 'Cadastrar Produto',
    'edit' => 'Editar Produto',
    'show' => 'Detalhes do Produto',
    'list' => 'Listagem de Produtos',

    'fields' => [
        'uuid' => 'UUID Público',
        'company' => 'Empresa Proprietária',
        'category' => 'Categoria',
        'sku' => 'SKU/Código Único',
        'barcode' => 'Código de Barras',
        'name' => 'Nome do Produto',
        'description' => 'Descrição / Ingredientes',
        'price' => 'Preço de Venda',
        'cost' => 'Preço de Custo (Opcional)',
        'status' => 'Status Administrativo',
        'preparation_time' => 'Tempo de Preparo (minutos)',
        'created_at' => 'Data de Cadastro',
    ],

    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],

    'messages' => [
        'create_success' => 'Produto cadastrado com sucesso!',
        'update_success' => 'Produto atualizado com sucesso!',
        'delete_success' => 'Produto excluído com sucesso!',
    ],
];
