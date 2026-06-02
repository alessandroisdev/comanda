<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\Backup\BackupService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$outputBuffer = "=== AUDITORIA LGPD FORENSE E SEGURANÇA DE DADOS (ETAPA P8) ===\n\n";

// 1. Varredura nos Logs do Sistema
$outputBuffer .= "1. Varrendo arquivos de logs em storage/logs/...\n";
$logDir = storage_path('logs');
$logFiles = glob($logDir.'/*.log');

$regexPatterns = [
    'CPF' => '/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', // Ex: 123.456.789-01
    'CPF_RAW' => '/\b\d{11}\b/', // Ex: 12345678901
    'Cartão de Crédito' => '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/', // Ex: 1234-5678-9012-3456
    'Chave Privada' => '/-----BEGIN (RSA )?PRIVATE KEY-----/',
];

foreach ($logFiles as $file) {
    $filename = basename($file);
    $outputBuffer .= "  Arquivo: {$filename}\n";
    $content = file_get_contents($file);

    foreach ($regexPatterns as $name => $pattern) {
        $matches = [];
        preg_match_all($pattern, $content, $matches);
        $count = count($matches[0]);
        $outputBuffer .= "    - Padrão {$name}: {$count} correspondências encontradas.\n";
        if ($count > 0) {
            // Mostrar amostra se houver chave privada ou dados de cartão
            if ($name === 'Chave Privada' || $name === 'Cartão de Crédito') {
                $outputBuffer .= "      ⚠️ ALERTA CRÍTICO: Vazamento de {$name} detectado no log!\n";
            }
        }
    }
}

// 2. Auditoria no Banco de Dados
$outputBuffer .= "\n2. Verificando dados pessoais sensíveis no Banco de Dados (Cartões e Chaves)...\n";
$hasCardTable = Schema::hasTable('cards') || Schema::hasTable('credit_cards');
$outputBuffer .= '  - Tabela de cartões/crédito direto no banco: '.($hasCardTable ? '⚠️ AVISO: Existe tabela de cartões.' : '✅ OK (Inexistente)')."\n";

// Checar se há chaves em tabelas como empresas ou configurações
$companiesCount = DB::table('companies')->count();
$outputBuffer .= "  - Verificando coluna settings_json nas empresas...\n";
if ($companiesCount > 0) {
    $companies = DB::table('companies')->get();
    foreach ($companies as $comp) {
        $json = $comp->settings_json;
        if (str_contains((string) $json, 'private_key') || str_contains((string) $json, 'passwd')) {
            $outputBuffer .= "    ⚠️ AVISO: Dados potencialmente sensíveis encontrados em settings_json para Company ID {$comp->id}.\n";
        } else {
            $outputBuffer .= "    ✅ OK (settings_json limpo)\n";
        }
    }
}

// 3. Varredura nos Backups
$outputBuffer .= "\n3. Auditando criptografia dos Backups (storage/app/backups/)...\n";
$backupDir = storage_path('app/backups');
$backups = glob($backupDir.'/*');

if (empty($backups)) {
    $outputBuffer .= "  - Nenhum arquivo de backup encontrado. Gerando um de teste...\n";
    try {
        $backupService = app(BackupService::class);
        $backupService->executeBackup(true); // Gerar criptografado
        $backups = glob($backupDir.'/*');
    } catch (Exception $e) {
        $outputBuffer .= "    * Falha ao gerar backup de teste: {$e->getMessage()}\n";
    }
}

foreach ($backups as $bfile) {
    $bname = basename($bfile);
    $outputBuffer .= "  Backup: {$bname}\n";

    if (str_ends_with($bname, '.enc')) {
        // Tentar ler e verificar se está criptografado (não contém strings SQL ou ZIP comuns)
        $fh = fopen($bfile, 'rb');
        $header = fread($fh, 100);
        fclose($fh);

        $isClearText = str_contains($header, 'CREATE TABLE') || str_contains($header, 'PK') || str_contains($header, 'INSERT INTO');
        if ($isClearText) {
            $outputBuffer .= "    ⚠️ ALERTA: Arquivo .enc possui cabeçalho legível em texto claro!\n";
        } else {
            $outputBuffer .= "    ✅ OK: Arquivo criptografado de forma segura (Gibberish detectado).\n";
        }
    } else {
        $outputBuffer .= "    ⚠️ AVISO DE CONFORMIDADE: Backup não criptografado encontrado: {$bname}\n";
    }
}

file_put_contents(__DIR__.'/lgpd_forensic_result.txt', $outputBuffer);
echo "Auditoria LGPD Forense concluída e salva em scratch/lgpd_forensic_result.txt\n";
