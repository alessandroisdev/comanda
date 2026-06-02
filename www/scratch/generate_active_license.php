<?php

declare(strict_types=1);

use App\Services\Licensing\LicenseManager;
use App\Services\Licensing\LicenseValidator;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$validator = app(LicenseValidator::class);
$manager = app(LicenseManager::class);

$licensePath = storage_path('app/license.json');
$keysDir = storage_path('app/keys');
$privKeyPath = $keysDir.'/license_private.key';
$pubKeyPath = $keysDir.'/license_public.key';

if (! file_exists($privKeyPath)) {
    echo "Erro: chave privada nao encontrada em $privKeyPath\n";
    exit(1);
}

$privateKey = file_get_contents($privKeyPath);
$localUuid = $validator->getLocalInstallationUuid();

echo "UUID local da instalacao: $localUuid\n";

$data = [
    'id' => 1,
    'installation_uuid' => $localUuid,
    'expires_at' => now()->addYears(2)->toIso8601String(),
    'status' => 'active',
    'modules' => ['kiosk', 'tablet_table', 'delivery'],
];

unset($data['signature']);
ksort($data);
$canonical = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

if (! openssl_sign($canonical, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
    echo "Erro ao assinar a licenca!\n";
    exit(1);
}

$data['signature'] = base64_encode($signature);

file_put_contents($licensePath, json_encode($data, JSON_PRETTY_PRINT));
echo "Licenca ativada com sucesso e salva em $licensePath!\n";

$manager->clearCache();
$status = $manager->getStatus();
echo 'Status da licenca validado: '.$status->value."\n";
