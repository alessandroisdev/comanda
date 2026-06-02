<?php

declare(strict_types=1);

namespace App\Services\Backup;

use App\Models\Backup;
use App\Models\BackupExecution;
use App\Services\Logging\AuditLogService;
use Carbon\Carbon;
use Exception;
use ZipArchive;

class RestoreService
{
    private string $tempDir;

    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->tempDir = storage_path('app/backup_temp');
        $this->auditLog = $auditLog;
    }

    /**
     * Executa a restauração completa do backup com integridade verificada.
     */
    public function executeRestore(Backup $backup): void
    {
        $startedAt = Carbon::now();
        $execution = BackupExecution::create([
            'type' => 'restore',
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        $filePath = storage_path('app/'.$backup->path);

        try {
            // 1. Verificar integridade física por Checksum SHA-256
            if (! file_exists($filePath)) {
                throw new Exception("Arquivo de backup {$backup->filename} não encontrado no storage.");
            }

            $currentChecksum = hash_file('sha256', $filePath);
            if ($currentChecksum !== $backup->checksum) {
                throw new Exception('Falha de integridade do backup: checksum SHA-256 divergente.');
            }

            // 2. Limpar e criar diretório temporário
            $this->cleanTempDir();
            @mkdir($this->tempDir, 0755, true);

            $zipPath = $filePath;

            // 3. Se criptografado, descriptografa para um ZIP temporário
            if ($backup->is_encrypted) {
                $zipPath = $this->tempDir.'/temp_decrypted.zip';
                $this->decryptFile($filePath, $zipPath);
            }

            // 4. Descompactar arquivos do ZIP final
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new Exception('Falha ao abrir arquivo ZIP de backup.');
            }
            $zip->extractTo($this->tempDir);
            $zip->close();

            $dbFile = $this->tempDir.'/db_backup.sql';
            $storageZipFile = $this->tempDir.'/storage_backup.zip';

            if (! file_exists($dbFile) || ! file_exists($storageZipFile)) {
                throw new Exception('Estrutura interna do backup corrompida ou incompleta.');
            }

            // 5. Restaurar banco de dados
            $this->restoreDatabase($dbFile);

            // 6. Restaurar storage de arquivos
            $this->restoreStorage($storageZipFile);

            // 7. Limpar diretório temporário
            $this->cleanTempDir();

            // 8. Atualizar execução
            $execution->update([
                'status' => 'success',
                'finished_at' => Carbon::now(),
            ]);

            $this->auditLog->log('backup.restore', "Restauração do backup {$backup->filename} executada com sucesso.", [
                'backup_id' => $backup->id,
            ]);

        } catch (Exception $e) {
            $execution->update([
                'status' => 'failed',
                'finished_at' => Carbon::now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->auditLog->log('backup.restore_failed', "Falha ao restaurar backup {$backup->filename}: {$e->getMessage()}", [
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

    private function decryptFile(string $sourcePath, string $outputPath): void
    {
        $fileData = file_get_contents($sourcePath);
        if ($fileData === false || strlen($fileData) < 16) {
            throw new Exception('Conteúdo criptografado do backup inválido ou truncado.');
        }

        $iv = substr($fileData, 0, 16);
        $encryptedData = substr($fileData, 16);

        $key = config('app.backup_key') ?: env('BACKUP_ENCRYPTION_KEY') ?: config('app.key');
        $keyHash = hash('sha256', $key, true);

        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $keyHash, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new Exception('Falha ao descriptografar arquivo de backup. Chave incorreta.');
        }

        file_put_contents($outputPath, $decrypted);
    }

    private function restoreDatabase(string $sqlPath): void
    {
        if (config('database.default') === 'sqlite') {
            return;
        }

        $host = config('database.connections.mysql.host', 'mysql');
        $user = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', 'root');
        $database = config('database.connections.mysql.database', 'comanda');

        $cmd = sprintf(
            'mysql --ssl=0 -h %s -u %s -p%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlPath)
        );

        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            throw new Exception("Falha ao importar dump SQL (código {$returnCode}): {$errorMsg}");
        }
    }

    private function restoreStorage(string $zipPath): void
    {
        $destPath = storage_path('app/public');
        if (! is_dir($destPath)) {
            @mkdir($destPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Falha ao abrir arquivo ZIP de storage interno.');
        }
        $zip->extractTo($destPath);
        $zip->close();
    }
}
