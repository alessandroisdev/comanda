<?php

declare(strict_types=1);

use App\Services\Audit\AuditService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Log;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== TESTANDO SANITIZAÇÃO DE LOGS (LGPD) ===\n\n";

// Limpar log do dia truncando o arquivo
$logPath = storage_path('logs/laravel-'.date('Y-m-d').'.log');
@file_put_contents($logPath, '');
$laravelLog = storage_path('logs/laravel.log');
@file_put_contents($laravelLog, '');

echo "Arquivos de log esvaziados para a auditoria.\n\n";

// 1. Log comum via Log::info
Log::info('Mensagem contendo CPF 123.456.789-01 e email teste@comanda.com');

// 2. Log de Auditoria via AuditService
$audit = app(AuditService::class);
$audit->log('user.update_profile',
    ['name' => 'John Doe', 'cpf' => '98765432109', 'email' => 'john@doe.com'],
    ['name' => 'John Doe Changed', 'cpf' => '98765432109', 'email' => 'john.new@doe.com', 'phone' => '11988888888'],
    ['context_token' => 'secret_token_value_123']
);

// 3. Forçar gravação de log de exceção
try {
    throw new Exception('Falha de teste com dados do cliente email=maria@gmail.com e cpf=123.456.789-01');
} catch (Exception $e) {
    Log::error($e->getMessage(), ['exception' => $e]);
}

// 4. Ler o conteúdo gravado e realizar varredura de PII em texto claro
$content = '';
if (file_exists($logPath)) {
    $content .= file_get_contents($logPath);
}
if (file_exists($laravelLog)) {
    $content .= file_get_contents($laravelLog);
}

echo "Varrendo log gerado...\n";

$rawCpfRegex = '/\b\d{11}\b/';
$formattedCpfRegex = '/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/';
$emailRegex = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
$phoneRegex = '/\b119\d{8}\b/';

// CPF Raw
preg_match_all($rawCpfRegex, $content, $rawCpfMatches);
$rawCpfMatchesCount = 0;
foreach ($rawCpfMatches[0] as $match) {
    if (isValidCpf($match)) {
        echo "Matched RAW CPF: [ {$match} ]\n";
        $rawCpfMatchesCount++;
    }
}

// CPF Formatted
preg_match_all($formattedCpfRegex, $content, $formattedCpfMatches);
foreach ($formattedCpfMatches[0] as $match) {
    echo "Matched Formatted CPF: [ {$match} ]\n";
}

// Emails
preg_match_all($emailRegex, $content, $emailMatches);
$emailMatchesClean = [];
foreach ($emailMatches[0] as $match) {
    if (! str_contains($match, 'mlocati') && ! str_contains($match, 'composer')) {
        echo "Matched Email: [ {$match} ]\n";
        $emailMatchesClean[] = $match;
    }
}

// Phones
preg_match_all($phoneRegex, $content, $phoneMatches);
foreach ($phoneMatches[0] as $match) {
    echo "Matched Phone: [ {$match} ]\n";
}

echo "\nResultados da Auditoria de Logs:\n";
echo '  - CPFs puros em texto claro: '.$rawCpfMatchesCount." (Esperado: 0)\n";
echo '  - CPFs formatados em texto claro: '.count($formattedCpfMatches[0])." (Esperado: 0)\n";
echo '  - E-mails em texto claro: '.count($emailMatchesClean)." (Esperado: 0)\n";
echo '  - Telefones (11988888888) em texto claro: '.count($phoneMatches[0])." (Esperado: 0)\n\n";

if ($rawCpfMatchesCount === 0 && count($formattedCpfMatches[0]) === 0 && count($emailMatchesClean) === 0 && count($phoneMatches[0]) === 0) {
    echo "✅ SUCESSO: Logs 100% sanitizados para PII (LGPD Compliance).\n";
} else {
    echo "❌ FALHA: Vazamento de PII detectado nos logs!\n";
    echo "Amostra do log gravado:\n";
    echo substr($content, 0, 1500)."\n...\n";
}

function isValidCpf(string $cpf): bool
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) !== 11) {
        return false;
    }
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += (int) $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ((int) $cpf[$c] !== $d) {
            return false;
        }
    }

    return true;
}
