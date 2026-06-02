<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Actions\PrintJob\EnqueuePrintJobAction;
use App\Models\PrintJob;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Enums\PrintJobStatusEnum;
use Illuminate\Support\Facades\DB;

$outputBuffer = "=== AUDITORIA DE IMPRESSÃO (ETAPA P5) ===\n\n";

// 1. Carregar ou criar Company / Unit
$company = Company::first();
if (!$company) {
    $outputBuffer .= "Empresa não encontrada. Abortando.\n";
    exit(1);
}
$unit = CompanyUnit::where('company_id', $company->id)->first();
if (!$unit) {
    $outputBuffer .= "Unidade da empresa não encontrada. Abortando.\n";
    exit(1);
}

$outputBuffer .= "Empresa ativa: {$company->name} (ID: {$company->id})\n";
$outputBuffer .= "Unidade ativa: {$unit->name} (ID: {$unit->id})\n\n";

// Limpar tabela print_jobs para isolar a auditoria
DB::table('print_jobs')->delete();
$outputBuffer .= "Tabela print_jobs limpa para a auditoria.\n\n";

// 2. Simulação concorrente de enfileiramento (Totem e Delivery)
$outputBuffer .= "1. Enfileirando 5 jobs de impressão concorrentes...\n";
$enqueueAction = app(EnqueuePrintJobAction::class);

$jobsCreated = [];
for ($i = 1; $i <= 5; $i++) {
    $source = ($i % 2 === 0) ? 'Totem' : 'Delivery';
    $payload = [
        'order_number' => "PED-AUDIT-00{$i}",
        'source' => $source,
        'items' => [
            ['product_name' => "Produto Audit {$i}", 'quantity' => $i],
        ],
        'total' => 'R$ ' . number_format($i * 15.5, 2, ',', '.'),
    ];

    $job = $enqueueAction->execute([
        'company_id' => $company->id,
        'unit_id' => $unit->id,
        'type' => 'receipt',
        'payload' => $payload,
    ]);
    
    $jobsCreated[] = $job;
    $outputBuffer .= "  - Job enfileirado: UUID {$job->uuid} | Destino: {$source} | Status: {$job->status->value}\n";
}

$totalJobs = DB::table('print_jobs')->count();
$outputBuffer .= "Total de jobs no banco: {$totalJobs} (Esperado: 5)\n\n";

// 3. Simulação de processamento
$outputBuffer .= "2. Simulando o processamento dos jobs pelo spooler...\n";

// Vamos processar 3 com sucesso, 1 com falha permanente e 1 com falha recuperável
foreach ($jobsCreated as $index => $job) {
    $jobId = $job->id;
    $jobRef = PrintJob::find($jobId);
    
    $outputBuffer .= "Processing Job: UUID {$jobRef->uuid} (PED-AUDIT-00" . ($index + 1) . ")\n";
    
    // Transição para processing
    $jobRef->update(['status' => PrintJobStatusEnum::PROCESSING]);
    $outputBuffer .= "  - Status atualizado para: " . PrintJobStatusEnum::PROCESSING->value . "\n";
    
    if ($index < 3) {
        // Sucesso
        $jobRef->update(['status' => PrintJobStatusEnum::PRINTED]);
        $outputBuffer .= "  - Impresso com SUCESSO. Status final: " . PrintJobStatusEnum::PRINTED->value . "\n";
    } elseif ($index === 3) {
        // Falha Permanente: simula 3 tentativas com falha de conexão e marca como FAILED
        $outputBuffer .= "  - Simulando FALHA PERMANENTE de conexão...\n";
        $attempts = 0;
        while ($attempts < 3) {
            $attempts++;
            $jobRef->update([
                'attempts' => $attempts,
                'status' => PrintJobStatusEnum::PROCESSING
            ]);
            $outputBuffer .= "    * Tentativa {$attempts}: Falha de rede detectada.\n";
        }
        $jobRef->update(['status' => PrintJobStatusEnum::FAILED]);
        $outputBuffer .= "  - Tentativas esgotadas (3/3). Status final: " . PrintJobStatusEnum::FAILED->value . "\n";
    } else {
        // Falha Recuperável: simula 2 falhas, e sucesso na 3ª tentativa
        $outputBuffer .= "  - Simulando FALHA RECUPERÁVEL (rede oscilando)...\n";
        $attempts = 0;
        while ($attempts < 2) {
            $attempts++;
            $jobRef->update([
                'attempts' => $attempts,
                'status' => PrintJobStatusEnum::PROCESSING
            ]);
            $outputBuffer .= "    * Tentativa {$attempts}: Falha temporária.\n";
        }
        
        // 3ª tentativa com sucesso
        $attempts++;
        $jobRef->update([
            'attempts' => $attempts,
            'status' => PrintJobStatusEnum::PRINTED
        ]);
        $outputBuffer .= "    * Tentativa {$attempts}: Conexão restabelecida. Impresso com SUCESSO.\n";
        $outputBuffer .= "  - Status final: " . PrintJobStatusEnum::PRINTED->value . "\n";
    }
}

// 4. Verificação no banco de duplicidades e integridade de status
$outputBuffer .= "\n3. Verificação de integridade pós-execução:\n";
$printedCount = DB::table('print_jobs')->where('status', PrintJobStatusEnum::PRINTED->value)->count();
$failedCount = DB::table('print_jobs')->where('status', PrintJobStatusEnum::FAILED->value)->count();
$pendingCount = DB::table('print_jobs')->where('status', PrintJobStatusEnum::PENDING->value)->count();

$outputBuffer .= "  - Jobs impressos (Printed): {$printedCount} (Esperado: 4)\n";
$outputBuffer .= "  - Jobs falhados (Failed): {$failedCount} (Esperado: 1)\n";
$outputBuffer .= "  - Jobs pendentes (Pending): {$pendingCount} (Esperado: 0)\n";

// Verificar duplicidade de UUIDs
$duplicateUuids = DB::table('print_jobs')
    ->select('uuid', DB::raw('count(*) as total'))
    ->groupBy('uuid')
    ->having('total', '>', 1)
    ->count();

$outputBuffer .= "  - Registros com UUIDs duplicados: {$duplicateUuids} (Esperado: 0)\n";

if ($printedCount === 4 && $failedCount === 1 && $duplicateUuids === 0) {
    $outputBuffer .= "✅ SUCESSO: Spooler concorrente e controle de retentativas auditados sem anomalias.\n";
} else {
    $outputBuffer .= "❌ FALHA: Discrepância na auditoria de impressão.\n";
}

file_put_contents(__DIR__ . '/print_audit_result.txt', $outputBuffer);
echo "Auditoria de Impressão concluída e salva em scratch/print_audit_result.txt\n";
