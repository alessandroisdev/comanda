<?php

namespace App\Enums;

enum LicenseStatusEnum: string
{
    case ACTIVE = 'active';
    case TRIAL = 'trial';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';
    case REVOKED = 'revoked';
    case INVALID = 'invalid';

    /**
     * Retorna a descrição traduzível do status.
     */
    public function label(): string
    {
        return __("licenses.status.{$this->value}");
    }

    /**
     * Verifica se a licença está em estado operacional ativo.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE || $this === self::TRIAL;
    }
}
