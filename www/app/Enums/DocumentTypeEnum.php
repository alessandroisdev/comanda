<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentTypeEnum: string
{
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';
}
