<?php

declare(strict_types=1);

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\LicenseAuditLog;
use Carbon\Carbon;
use Exception;

class LicenseIssuerService
{
    private KeyGeneratorService $keyGenerator;

    public function __construct(KeyGeneratorService $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * Emite e assina criptograficamente uma licença no Manager.
     */
    public function issue(License $license, array $modulesKeys, string $installationUuid, ?string $auditAction = 'issue'): string
    {
        $privateKeyStr = $this->keyGenerator->getPrivateKey();

        // Se a chave privada não existir, gera o par automaticamente
        if (! $privateKeyStr) {
            $keys = $this->keyGenerator->generate(true);
            $privateKeyStr = $keys['private_key'];
        }

        $privateKeyResource = openssl_pkey_get_private($privateKeyStr);
        if (! $privateKeyResource) {
            throw new Exception('Chave privada RSA inválida ou corrompida no Manager.');
        }

        // 1. Estrutura o payload da licença de acordo com os padrões da especificação
        $payload = [
            'license_uuid' => $license->uuid,
            'installation_uuid' => $installationUuid,
            'client_uuid' => $license->uuid, // UUID do cliente/licença
            'client_name' => $license->client_name,
            'client_email' => $license->client_email,
            'client_document' => $license->client_document,
            'plan_name' => $license->plan_name,
            'type' => $license->type,
            'modules' => $modulesKeys,
            'issued_at' => $license->issued_at ? $license->issued_at->toIso8601String() : Carbon::now()->toIso8601String(),
            'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : Carbon::now()->addYear()->toIso8601String(),
            'status' => $license->status,
        ];

        // 2. Ordena as chaves determinística e canonicamente
        ksort($payload);
        $canonicalJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // 3. Assina o conteúdo canônico usando a chave privada RSA
        $signature = '';
        $success = openssl_sign($canonicalJson, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        if (! $success) {
            throw new Exception('Falha ao gerar assinatura digital da licença.');
        }

        // 4. Adiciona a assinatura criptográfica em base64 no payload original
        $payload['signature'] = base64_encode($signature);

        // 5. Gera a chave de ativação final encapsulada em base64
        $activationKey = base64_encode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // 6. Atualiza o banco do manager com a chave de ativação gerada
        $license->update([
            'key_data' => $activationKey,
            'issued_at' => $license->issued_at ?? Carbon::now(),
            'expires_at' => $license->expires_at ?? Carbon::now()->addYear(),
        ]);

        // Registrar log de auditoria comercial imutável se solicitado
        if ($auditAction !== null) {
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'installation_uuid' => $installationUuid,
                'action' => $auditAction,
                'details' => [
                    'type' => $license->type,
                    'plan_name' => $license->plan_name,
                    'modules' => $modulesKeys,
                    'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $activationKey;
    }
}
