<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Licensing\LicenseValidator;
use App\Services\Licensing\LicenseManager;
use App\Enums\LicenseStatusEnum;
use Carbon\Carbon;

$outputBuffer = "=== AUDITORIA DE CONTROLE DE LICENCIAMENTO (ETAPA P7) ===\n\n";

$validator = app(LicenseValidator::class);
$manager = app(LicenseManager::class);

$licensePath = storage_path('app/license.json');
$keysDir = storage_path('app/keys');
$pubKeyPath = $keysDir . '/license_public.key';

// 1. Fazer backup da licença e chaves originais
$origLicense = file_exists($licensePath) ? file_get_contents($licensePath) : null;
$origPubKey = file_exists($pubKeyPath) ? file_get_contents($pubKeyPath) : null;
$origPrivKey = file_exists($keysDir . '/license_private.key') ? file_get_contents($keysDir . '/license_private.key') : null;

$outputBuffer .= "Realizado backup temporário das credenciais de licença existentes.\n\n";

// 2. Gerar chaves de teste
$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];
$res = openssl_pkey_new($config);
openssl_pkey_export($res, $testPrivateKey);
$publicKeyDetails = openssl_pkey_get_details($res);
$testPublicKey = $publicKeyDetails['key'];

// Gravar chaves de teste no diretório
if (!is_dir($keysDir)) {
    mkdir($keysDir, 0755, true);
}
file_put_contents($pubKeyPath, $testPublicKey);
file_put_contents($keysDir . '/license_private.key', $testPrivateKey);

$localUuid = $validator->getLocalInstallationUuid();
$outputBuffer .= "UUID de Instalação Local: {$localUuid}\n\n";

// Helper para assinar e salvar licença
$signAndSave = function(array $data) use ($testPrivateKey, $licensePath) {
    unset($data['signature']);
    ksort($data);
    $canonical = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    openssl_sign($canonical, $signature, $testPrivateKey, OPENSSL_ALGO_SHA256);
    $data['signature'] = base64_encode($signature);
    
    file_put_contents($licensePath, json_encode($data, JSON_PRETTY_PRINT));
    return $data;
};

// Cenário 1: Licença Válida (Active)
$outputBuffer .= "Cenário 1: Licença Válida\n";
$signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->addMonth()->toIso8601String(),
    'status' => 'active',
    'modules' => ['kiosk', 'tablet_table', 'delivery'],
]);
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: active)\n";
$outputBuffer .= "  - Expirando em breve? " . ($manager->isExpiringSoon() ? 'SIM' : 'NÃO') . " (Esperado: NÃO)\n\n";

// Cenário 2: Licença Vencida no Grace Period (3 dias atrás)
$outputBuffer .= "Cenário 2: Licença Vencida dentro do Grace Period (Tolerância)\n";
$signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->subDays(3)->toIso8601String(),
    'status' => 'active',
    'modules' => ['kiosk', 'tablet_table', 'delivery'],
]);
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: active - operando sob grace period)\n";
$outputBuffer .= "  - Carência ativa? " . ($manager->isOperatingInGracePeriod() ? 'SIM' : 'NÃO') . " (Esperado: SIM)\n\n";

// Cenário 3: Licença Expirada (10 dias atrás - fora do Grace Period)
$outputBuffer .= "Cenário 3: Licença Expirada fora do Grace Period\n";
$signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->subDays(10)->toIso8601String(),
    'status' => 'active',
    'modules' => ['kiosk', 'tablet_table', 'delivery'],
]);
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: expired)\n\n";

// Cenário 4: Licença Suspensa
$outputBuffer .= "Cenário 4: Licença Suspensa comercialmente\n";
$signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->addMonth()->toIso8601String(),
    'status' => 'suspended',
    'modules' => ['kiosk', 'tablet_table', 'delivery'],
]);
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: suspended)\n\n";

// Cenário 5: Licença Cancelada
$outputBuffer .= "Cenário 5: Licença Cancelada comercialmente\n";
$signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->addMonth()->toIso8601String(),
    'status' => 'cancelled',
    'modules' => ['kiosk', 'tablet_table', 'delivery'],
]);
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: cancelled)\n\n";

// Cenário 6: Licença Adulterada (Assinatura Inválida)
$outputBuffer .= "Cenário 6: Licença Adulterada (Modificação pós-assinatura)\n";
$licData = $signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->addMonth()->toIso8601String(),
    'status' => 'active',
    'modules' => ['kiosk'],
]);
// Adulterar arquivo alterando o array de modulos sem gerar nova assinatura
$licData['modules'] = ['kiosk', 'delivery'];
file_put_contents($licensePath, json_encode($licData, JSON_PRETTY_PRINT));

$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: invalid)\n\n";

// Cenário 7: Chave Pública Incorreta (Simula chave adulterada na instalação)
$outputBuffer .= "Cenário 7: Chave Pública Incorreta / Adulterada\n";
// Gerar outra chave qualquer e salvar como chave publica da aplicacao
$wrongRes = openssl_pkey_new($config);
$wrongPubKeyDetails = openssl_pkey_get_details($wrongRes);
file_put_contents($pubKeyPath, $wrongPubKeyDetails['key']);

$signAndSave([
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->addMonth()->toIso8601String(),
    'status' => 'active',
    'modules' => ['kiosk'],
]);
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: invalid)\n\n";

// Cenário 8: Licença Ausente
$outputBuffer .= "Cenário 8: Licença Ausente do Sistema\n";
if (file_exists($licensePath)) {
    unlink($licensePath);
}
$manager->clearCache();
$status = $manager->getStatus();
$outputBuffer .= "  - Status retornado: {$status->value} (Esperado: invalid)\n\n";


// 3. Restaurar credenciais originais
if ($origLicense) {
    file_put_contents($licensePath, $origLicense);
} else if (file_exists($licensePath)) {
    unlink($licensePath);
}

if ($origPubKey) {
    file_put_contents($pubKeyPath, $origPubKey);
} else if (file_exists($pubKeyPath)) {
    unlink($pubKeyPath);
}

if ($origPrivKey) {
    file_put_contents($keysDir . '/license_private.key', $origPrivKey);
} else if (file_exists($keysDir . '/license_private.key')) {
    unlink($keysDir . '/license_private.key');
}

$manager->clearCache();
$outputBuffer .= "Restaurado backup original das chaves e licença do sistema.\n";

file_put_contents(__DIR__ . '/licensing_audit_result.txt', $outputBuffer);
echo "Auditoria de Licenciamento concluída e salva em scratch/licensing_audit_result.txt\n";
