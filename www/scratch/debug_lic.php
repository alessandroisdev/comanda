<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Licensing\LicenseValidator;
use App\Services\Licensing\LicenseManager;

echo "=== DEPURAÇÃO DE VALIDAÇÃO DE LICENÇA (FASE 7) ===\n\n";

$validator = app(LicenseValidator::class);
$manager = app(LicenseManager::class);

$localUuid = $validator->getLocalInstallationUuid();
echo "Local Installation UUID: {$localUuid}\n";

$licenseData = $manager->getLicenseData();
if (!$licenseData) {
    die("ERRO: Nenhuma licença encontrada em storage/app/license.json\n");
}

echo "License Installation UUID: " . ($licenseData['installation_uuid'] ?? 'N/A') . "\n";
echo "UUIDs batem? " . (($licenseData['installation_uuid'] === $localUuid) ? 'SIM' : 'NÃO') . "\n";

$pubKeyPath = storage_path('app/keys/license_public.key');
echo "Caminho Chave Pública: {$pubKeyPath}\n";
echo "Chave Pública existe? " . (file_exists($pubKeyPath) ? 'SIM' : 'NÃO') . "\n";

if (file_exists($pubKeyPath)) {
    echo "Conteúdo da Chave Pública (primeiros 50 chars): " . substr(file_get_contents($pubKeyPath), 0, 50) . "...\n";
}

$signature = base64_decode($licenseData['signature'] ?? '');
unset($licenseData['signature']);
ksort($licenseData);
$canonical = json_encode($licenseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (file_exists($pubKeyPath)) {
    $pubKey = file_get_contents($pubKeyPath);
    $pubKeyResource = openssl_pkey_get_public($pubKey);
    if (!$pubKeyResource) {
        echo "ERRO:openssl_pkey_get_public falhou ao ler a chave pública.\n";
    } else {
        $res = openssl_verify($canonical, $signature, $pubKeyResource, OPENSSL_ALGO_SHA256);
        echo "openssl_verify result (1 = VÁLIDO, 0 = INVÁLIDO, -1 = ERRO): {$res}\n";
    }
}

$status = $validator->validate($manager->getLicenseData() ?? []);
echo "Resultado do Validator: {$status->name}\n";
