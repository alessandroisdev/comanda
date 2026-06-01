<?php

return [
    'status' => [
        'active' => 'Active',
        'trial' => 'Trial Period',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
        'revoked' => 'Revoked',
        'invalid' => 'Invalid',
    ],
    'types' => [
        'trial' => 'Trial',
        'subscription' => 'Subscription',
        'perpetual' => 'Perpetual',
        'developer' => 'Developer',
        'internal' => 'Internal Use',
    ],
    'errors' => [
        'key_empty' => 'The license key cannot be empty.',
        'key_too_short' => 'The provided license key is too short.',
        'invalid_format' => 'The license file format is invalid.',
        'validation_failed' => 'License integrity validation failed.',
    ],
];
