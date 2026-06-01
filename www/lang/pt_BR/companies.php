<?php

declare(strict_types=1);

return [
    'title' => 'Empresas',
    'subtitle' => 'Gerenciamento de Locatários (Tenants) do Sistema',
    'fields' => [
        'uuid' => 'UUID',
        'status' => 'Status',
        'legal_name' => 'Razão Social',
        'trade_name' => 'Nome Fantasia',
        'document_type' => 'Tipo de Documento',
        'document_number' => 'Número do Documento',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'timezone' => 'Fuso Horário',
        'currency' => 'Moeda',
        'language' => 'Idioma',
        'logo' => 'Logo',
        'settings_json' => 'Configurações (JSON)',
        'created_at' => 'Criado em',
        'actions' => 'Ações',
    ],
    'status' => [
        'active' => 'Ativo',
        'suspended' => 'Suspenso',
    ],
    'messages' => [
        'create_success' => 'Empresa cadastrada com sucesso!',
        'update_success' => 'Empresa atualizada com sucesso!',
        'delete_success' => 'Empresa excluída com sucesso!',
    ],
];
