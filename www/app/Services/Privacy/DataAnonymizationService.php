<?php

declare(strict_types=1);

namespace App\Services\Privacy;

class DataAnonymizationService
{
    /**
     * Mascara um CPF no formato ***.***.***-**.
     */
    public function maskCpf(?string $cpf): string
    {
        if (empty($cpf)) {
            return '';
        }

        $clean = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($clean) !== 11) {
            return '***.***.***-**';
        }

        return '***.***.'.substr($clean, 6, 3).'-**';
    }

    /**
     * Mascara um e-mail no formato a***@dominio.com.
     */
    public function maskEmail(?string $email): string
    {
        if (empty($email)) {
            return '';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return 'a***@dominio.com';
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            return $name[0].'***@'.$domain;
        }

        return $name[0].str_repeat('*', strlen($name) - 2).substr($name, -1).'@'.$domain;
    }

    /**
     * Mascara um telefone no formato (***) *****-1234.
     */
    public function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);

        if ($len < 4) {
            return '(***) *****-****';
        }

        return '(***) *****-'.substr($clean, -4);
    }

    /**
     * Gera o valor anonimizado para nomes e dados pessoais.
     */
    public function anonymizeName(): string
    {
        return 'Titular Anonimizado';
    }
}
