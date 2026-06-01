<?php

namespace App\Enums;

enum LicenseTypeEnum: string
{
    case TRIAL = 'trial';
    case SUBSCRIPTION = 'subscription';
    case PERPETUAL = 'perpetual';
    case DEVELOPER = 'developer';
    case INTERNAL = 'internal';

    /**
     * Retorna a descrição traduzível do tipo de licença.
     */
    public function label(): string
    {
        return __("licenses.types.{$this->value}");
    }
}
