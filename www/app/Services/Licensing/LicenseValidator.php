<?php

namespace App\Services\Licensing;

use App\Enums\LicenseStatusEnum;
use Carbon\Carbon;
use Exception;

class LicenseValidator
{
    private string $publicKeyPath;

    public function __construct()
    {
        $this->publicKeyPath = storage_path('app/keys/license_public.key');
    }

    /**
     * Valida os dados da licença localmente utilizando assinatura digital RSA.
     *
     * @param  array  $licenseData  Payload contendo installation_uuid, expires_at, modules, signature, etc.
     */
    public function validate(array $licenseData): LicenseStatusEnum
    {
        try {
            if (empty($licenseData['signature']) || empty($licenseData['installation_uuid'])) {
                return LicenseStatusEnum::INVALID;
            }

            // 1. Validar UUID da instalação contra a instalação física local
            $localInstallationUuid = $this->getLocalInstallationUuid();
            if ($licenseData['installation_uuid'] !== $localInstallationUuid) {
                return LicenseStatusEnum::INVALID;
            }

            // 2. Validar integridade e assinatura criptográfica
            $signature = base64_decode($licenseData['signature']);
            unset($licenseData['signature']); // Remover assinatura para validar conteúdo original

            // Serializar os dados originais de forma canônica para verificação determinística
            ksort($licenseData);
            $canonicalData = json_encode($licenseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if (! $this->verifySignature($canonicalData, $signature)) {
                return LicenseStatusEnum::INVALID;
            }

            // 3. Validar limite de expiração temporal
            $expiresAt = Carbon::parse($licenseData['expires_at']);
            if (Carbon::now()->greaterThan($expiresAt)) {
                return LicenseStatusEnum::EXPIRED;
            }

            // 4. Retornar status operacional correspondente
            $statusStr = $licenseData['status'] ?? 'active';

            return LicenseStatusEnum::tryFrom($statusStr) ?? LicenseStatusEnum::ACTIVE;

        } catch (Exception $e) {
            // Em caso de erro estrutural grave, define como inválida
            return LicenseStatusEnum::INVALID;
        }
    }

    /**
     * Verifica a assinatura digital assimétrica RSA.
     */
    private function verifySignature(string $data, string $signature): bool
    {
        // Se a chave pública não existir fisicamente, aceitar modo desenvolvimento local
        if (! file_exists($this->publicKeyPath)) {
            // Apenas para testes iniciais de desenvolvimento local na Fase 1
            // Em produção a chave pública RSA deve estar presente no storage de chaves
            return true;
        }

        $publicKey = file_get_contents($this->publicKeyPath);
        $pubKeyResource = openssl_pkey_get_public($publicKey);

        if (! $pubKeyResource) {
            return false;
        }

        // openssl_verify retorna 1 se a assinatura for válida, 0 se incorreta, -1 em erro.
        $result = openssl_verify($data, $signature, $pubKeyResource, OPENSSL_ALGO_SHA256);

        if (is_resource($pubKeyResource) || $pubKeyResource instanceof \OpenSSLAsymmetricKey) {
            openssl_free_key($pubKeyResource);
        }

        return $result === 1;
    }

    /**
     * Obtém ou gera de forma persistente o UUID da instalação física local.
     */
    public function getLocalInstallationUuid(): string
    {
        $path = storage_path('app/installation_uuid');

        if (file_exists($path)) {
            return trim(file_get_contents($path));
        }

        // Gerar UUID v4 robusto se não existir
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF), mt_rand(0, 0xFFFF)
        );

        // Garantir criação do diretório storage/app antes
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $uuid);

        return $uuid;
    }
}
