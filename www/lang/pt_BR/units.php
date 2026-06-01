<?php

declare(strict_types=1);

return [
    'title' => 'Unidades',
    'subtitle' => 'Gerenciamento de Filiais e Unidades Físicas das Empresas',
    'fields' => [
        'uuid' => 'UUID',
        'company' => 'Empresa',
        'status' => 'Status',
        'name' => 'Nome da Unidade',
        'document_number' => 'CNPJ da Unidade',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'zipcode' => 'CEP',
        'street' => 'Logradouro',
        'number' => 'Número',
        'district' => 'Bairro',
        'city' => 'Cidade',
        'state' => 'Estado',
        'country' => 'País',
        'settings_json' => 'Configurações (JSON)',
        'created_at' => 'Criado em',
        'actions' => 'Ações',
        'location' => 'Cidade / Estado',
    ],
    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],
    'messages' => [
        'create_success' => 'Unidade cadastrada com sucesso!',
        'update_success' => 'Unidade atualizada com sucesso!',
        'delete_success' => 'Unidade excluída com sucesso!',
    ],
];
