<?php

declare(strict_types=1);

namespace App\Services\Licensing;

use Exception;

class KeyGeneratorService
{
    private string $privateKeyPath;

    private string $publicKeyPath;

    public function __construct()
    {
        $this->privateKeyPath = storage_path('app/keys/license_private.key');
        $this->publicKeyPath = storage_path('app/keys/license_public.key');
    }

    /**
     * Gera o par de chaves RSA-2048 e as persiste localmente.
     */
    public function generate(bool $force = false): array
    {
        if (! $force && file_exists($this->privateKeyPath) && file_exists($this->publicKeyPath)) {
            return [
                'private_key' => file_get_contents($this->privateKeyPath),
                'public_key' => file_get_contents($this->publicKeyPath),
            ];
        }

        // Configuração para geração da chave RSA de 2048 bits
        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Cria o par de chaves
        $res = openssl_pkey_new($config);
        if (! $res) {
            throw new Exception('Erro ao inicializar par de chaves OpenSSL.');
        }

        // Exporta a chave privada
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);

        // Exporta a chave pública
        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'] ?? '';

        if (empty($privateKey) || empty($publicKey)) {
            throw new Exception('Erro ao exportar chaves OpenSSL.');
        }

        // Garante o diretório seguro
        $directory = dirname($this->privateKeyPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        // Salva as chaves
        file_contents_replace($this->privateKeyPath, $privateKey);
        file_contents_replace($this->publicKeyPath, $publicKey);

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
        ];
    }

    public function getPrivateKey(): ?string
    {
        if (file_exists($this->privateKeyPath)) {
            return file_get_contents($this->privateKeyPath);
        }

        return null;
    }

    public function getPublicKey(): ?string
    {
        if (file_exists($this->publicKeyPath)) {
            return file_get_contents($this->publicKeyPath);
        }

        return null;
    }
}

// Função auxiliar segura caso não exista ou para evitar problemas
if (! function_exists('file_contents_replace')) {
    function file_contents_replace(string $path, string $data): void
    {
        file_put_contents($path, $data);
        chmod($path, 0600);
    }
}
