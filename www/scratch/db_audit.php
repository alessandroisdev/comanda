<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$outputBuffer = "=== AUDITORIA DE BANCO DE DADOS (ETAPA P1) ===\n\n";

function auditDatabase($connectionName, $dbName, &$outputBuffer)
{
    $outputBuffer .= "--- Banco de Dados: {$dbName} ({$connectionName}) ---\n";

    // Obter todas as tabelas
    $tables = DB::connection($connectionName)->select("SHOW TABLES FROM `{$dbName}`");
    $tablesKey = "Tables_in_{$dbName}";

    foreach ($tables as $tObj) {
        $tableName = $tObj->$tablesKey;

        // 1. Quantidade de registros
        $count = DB::connection($connectionName)->table($tableName)->count();

        // 2. Índices
        $indices = DB::connection($connectionName)->select("SHOW INDEX FROM `{$tableName}`");
        $indexNames = [];
        $indexedColumns = [];
        foreach ($indices as $idx) {
            $indexNames[$idx->Key_name] = ($idx->Non_unique == 0 ? 'UNIQUE' : 'INDEX');
            $indexedColumns[] = $idx->Column_name;
        }
        $indexedColumns = array_unique($indexedColumns);

        // 3. Foreign Keys
        $fkQuery = '
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ';
        $fks = DB::connection($connectionName)->select($fkQuery, [$dbName, $tableName]);

        // 4. Colunas sem índice e críticas sem índice
        // Obter todas as colunas
        $columns = Schema::connection($connectionName)->getColumnListing($tableName);
        $unindexedColumns = [];
        $criticalUnindexed = [];

        foreach ($columns as $col) {
            if (! in_array($col, $indexedColumns) && $col !== 'id') {
                $unindexedColumns[] = $col;

                // Critérios de colunas críticas sem índice:
                if (
                    str_ends_with($col, '_id') ||
                    str_ends_with($col, '_uuid') ||
                    in_array($col, ['status', 'type', 'active', 'deleted_at', 'email', 'cpf', 'cnpj', 'slug'])
                ) {
                    $criticalUnindexed[] = $col;
                }
            }
        }

        $outputBuffer .= "Tabela: {$tableName}\n";
        $outputBuffer .= "  Registros: {$count}\n";
        $outputBuffer .= '  Índices: '.(empty($indexNames) ? 'Nenhum' : implode(', ', array_map(fn ($k, $v) => "{$k} ({$v})", array_keys($indexNames), $indexNames)))."\n";
        $outputBuffer .= '  Foreign Keys: '.(empty($fks) ? 'Nenhuma' : implode(', ', array_map(fn ($f) => "{$f->COLUMN_NAME} -> {$f->REFERENCED_TABLE_NAME}({$f->REFERENCED_COLUMN_NAME})", $fks)))."\n";
        $outputBuffer .= '  Colunas sem índice: '.implode(', ', $unindexedColumns)."\n";
        if (! empty($criticalUnindexed)) {
            $outputBuffer .= '  ⚠️ CRÍTICAS SEM ÍNDICE: '.implode(', ', $criticalUnindexed)."\n";
        }
        $outputBuffer .= "\n";
    }
}

// Rodar para o Cliente (comanda)
auditDatabase('mysql', 'comanda', $outputBuffer);

// Rodar para o Manager (comanda_manager)
config(['database.connections.mysql_manager' => array_merge(
    config('database.connections.mysql'),
    ['database' => 'comanda_manager']
)]);
auditDatabase('mysql_manager', 'comanda_manager', $outputBuffer);

file_put_contents(__DIR__.'/db_audit_result.txt', $outputBuffer);
echo "Auditoria salva com sucesso em scratch/db_audit_result.txt\n";
