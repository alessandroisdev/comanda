<?php

return [
    'status' => [
        'active' => 'Activa',
        'trial' => 'Período de Prueba',
        'expired' => 'Expirada',
        'suspended' => 'Suspendida',
        'revoked' => 'Revocada',
        'invalid' => 'Inválida',
    ],
    'types' => [
        'trial' => 'Demostración (Trial)',
        'subscription' => 'Suscripción',
        'perpetual' => 'Vitalicia',
        'developer' => 'Desarrollo',
        'internal' => 'Uso Interno',
    ],
    'errors' => [
        'key_empty' => 'La clave de licencia no puede estar vacía.',
        'key_too_short' => 'La clave de licencia proporcionada es demasiado corta.',
        'invalid_format' => 'El formato del archivo de licencia no es válido.',
        'validation_failed' => 'Falló la validación de integridad de la licencia.',
    ],
];
