<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Exception;
use Illuminate\Console\Command;

class RunBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comanda:backup:run {--no-encrypt : Desativa a criptografia do backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa o backup completo de banco e storage de forma compactada e criptografada.';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $this->info('Iniciando o processo de backup do Comanda...');
        $encrypt = ! $this->option('no-encrypt');

        try {
            $backup = $backupService->executeBackup($encrypt);
            $this->info('Backup concluído com sucesso!');
            $this->line('Arquivo: '.$backup->filename);
            $this->line('Tamanho: '.round($backup->size_bytes / 1024 / 1024, 2).' MB');
            $this->line('Checksum (SHA-256): '.$backup->checksum);

            return 0;
        } catch (Exception $e) {
            $this->error('Erro ao realizar backup: '.$e->getMessage());

            return 1;
        }
    }
}
