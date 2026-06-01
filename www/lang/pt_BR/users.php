<?php

declare(strict_types=1);

return [
    'title' => 'Usuários',
    'subtitle' => 'Gerenciamento de Usuários Administrativos Corporativos',
    'fields' => [
        'uuid' => 'UUID',
        'status' => 'Status',
        'name' => 'Nome do Usuário',
        'email' => 'E-mail',
        'password' => 'Senha',
        'password_help' => 'Deixe em branco para manter a senha atual.',
        'created_at' => 'Criado em',
        'actions' => 'Ações',
    ],
    'status' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
    ],
    'messages' => [
        'create_success' => 'Usuário cadastrado com sucesso!',
        'update_success' => 'Usuário atualizado com sucesso!',
        'delete_success' => 'Usuário excluído com sucesso!',
    ],
];
