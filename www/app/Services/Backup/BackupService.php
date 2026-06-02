<?php

declare(strict_types=1);

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\BackupExecution;
use App\Services\Logging\AuditLogService;
use Carbon\Carbon;
use Exception;
use ZipArchive;

class BackupService
{
    private string $tempDir;

    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->tempDir = storage_path('app/backup_temp');
        $this->auditLog = $auditLog;
    }

    /**
     * Executa a rotina completa de backup (Banco + Storage) com compressão e criptografia.
     */
    public function executeBackup(bool $encrypt = true): Backup
    {
        $startedAt = Carbon::now();
        $execution = BackupExecution::create([
            'type' => 'backup',
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        try {
            // 1. Limpar e criar diretório temporário
            $this->cleanTempDir();
            @mkdir($this->tempDir, 0755, true);

            $timestamp = $startedAt->format('Y_m_d_His');
            $dbFile = $this->tempDir.'/db_backup.sql';
            $storageZipFile = $this->tempDir.'/storage_backup.zip';

            // 2. Dump do Banco de Dados
            $this->dumpDatabase($dbFile);

            // 3. Compactar uploads / public storage
            $this->zipStorage($storageZipFile);

            // 4. Compactar tudo em um único arquivo ZIP final
            $finalZipFile = storage_path("app/backups/backup_{$timestamp}.zip");
            @mkdir(dirname($finalZipFile), 0755, true);

            $zip = new ZipArchive;
            if ($zip->open($finalZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Falha ao criar o arquivo ZIP final do backup.');
            }
            $zip->addFile($dbFile, 'db_backup.sql');
            $zip->addFile($storageZipFile, 'storage_backup.zip');
            $zip->close();

            $outputFile = $finalZipFile;
            $isEncrypted = false;

            // 5. Criptografar o arquivo se solicitado
            if ($encrypt) {
                $encryptedFile = $finalZipFile.'.enc';
                $this->encryptFile($finalZipFile, $encryptedFile);
                @unlink($finalZipFile); // remove zip cru
                $outputFile = $encryptedFile;
                $isEncrypted = true;
            }

            // 6. Calcular metadados
            $size = filesize($outputFile);
            $checksum = hash_file('sha256', $outputFile);
            $filename = basename($outputFile);
            $relativePath = 'backups/'.$filename;

            // 7. Persistir metadados no banco
            $backup = Backup::create([
                'filename' => $filename,
                'path' => $relativePath,
                'disk' => 'local',
                'checksum' => $checksum,
                'size_bytes' => $size,
                'is_encrypted' => $isEncrypted,
            ]);

            // 8. Limpar diretório temporário
            $this->cleanTempDir();

            // 9. Atualizar execução
            $execution->update([
                'status' => 'success',
                'finished_at' => Carbon::now(),
            ]);

            $this->auditLog->log('backup.create', "Backup {$filename} gerado com sucesso.", [
                'backup_id' => $backup->id,
                'size_bytes' => $size,
                'is_encrypted' => $isEncrypted,
            ]);

            // 10. Aplicar políticas de retenção
            $this->applyRetention();

            return $backup;

        } catch (Exception $e) {
            $execution->update([
                'status' => 'failed',
                'finished_at' => Carbon::now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->auditLog->log('backup.failed', "Falha ao executar backup: {$e->getMessage()}", [
                'error' => $e->getMessage(),
            ]);

            $this->cleanTempDir();
            throw $e;
        }
    }

    private function cleanTempDir(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir.'/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($this->tempDir);
        }
    }

    private function dumpDatabase(string $outputPath): void
    {
        if (config('database.default') === 'sqlite') {
            file_put_contents($outputPath, '-- SQLite memory dump');

            return;
        }

        $host = config('database.connections.mysql.host', 'mysql');
        $user = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', 'root');
        $database = config('database.connections.mysql.database', 'comanda');

        // Comando mariadb-dump nativo mapeado no container
        $cmd = sprintf(
            'mariadb-dump --ssl=0 -h %s -u %s -p%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($outputPath)
        );

        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            throw new Exception("Falha no mysqldump (código {$returnCode}): {$errorMsg}");
        }
    }

    private function zipStorage(string $outputPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Falha ao criar o arquivo ZIP de storage.');
        }

        $sourcePath = storage_path('app/public');
        if (is_dir($sourcePath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourcePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (! $file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($sourcePath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        } else {
            // Adiciona arquivo dummy se pasta pública de uploads estiver vazia
            $zip->addFromString('placeholder.txt', 'Comanda storage backup placeholder');
        }

        $zip->close();
    }

    private function encryptFile(string $sourcePath, string $outputPath): void
    {
        $data = file_get_contents($sourcePath);
        if ($data === false) {
            throw new Exception('Não foi possível ler o arquivo ZIP original para criptografia.');
        }

        // Obtém chave de criptografia do .env ou usa app key como fallback
        $key = config('app.backup_key') ?: env('BACKUP_ENCRYPTION_KEY') ?: config('app.key');
        $keyHash = hash('sha256', $key, true); // chave de 256 bits

        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $keyHash, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new Exception('Falha na criptografia dos dados via openssl_encrypt.');
        }

        // O arquivo criptografado contém: [IV (16 bytes)][DADOS CRIPTOGRAFADOS]
        file_put_contents($outputPath, $iv.$encrypted);
    }

    private function applyRetention(): void
    {
        // Política de retenção fixa de 7 dias para evitar sobrecarga de disco
        $retentionDays = 7;
        $limitDate = Carbon::now()->subDays($retentionDays);

        $oldBackups = Backup::where('created_at', '<', $limitDate)->get();

        /** @var Backup $oldBackup */
        foreach ($oldBackups as $oldBackup) {
            $physicalPath = storage_path('app/'.$oldBackup->path);
            if (file_exists($physicalPath)) {
                @unlink($physicalPath);
            }
            $oldBackup->delete();
        }
    }
}
