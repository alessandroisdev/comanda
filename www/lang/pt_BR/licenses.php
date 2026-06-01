<?php

return [
    'status' => [
        'active' => 'Ativa',
        'trial' => 'Período de Testes',
        'expired' => 'Expirada',
        'suspended' => 'Suspensa',
        'revoked' => 'Revogada',
        'invalid' => 'Inválida',
    ],
    'types' => [
        'trial' => 'Demonstração (Trial)',
        'subscription' => 'Assinatura',
        'perpetual' => 'Vitalícia',
        'developer' => 'Desenvolvimento',
        'internal' => 'Uso Interno',
    ],
    'errors' => [
        'key_empty' => 'A chave de licença não pode estar vazia.',
        'key_too_short' => 'A chave de licença fornecida é muito curta.',
        'invalid_format' => 'O formato do arquivo de licença é inválido.',
        'validation_failed' => 'Falha na validação de integridade da licença.',
    ],
];
