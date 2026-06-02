<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\Backup\RestoreService;
use Exception;
use Illuminate\Console\Command;

class RestoreBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comanda:backup:restore {id : O ID do backup a ser restaurado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaura o banco de dados e arquivos de storage a partir de um backup registrado.';

    /**
     * Execute the console command.
     */
    public function handle(RestoreService $restoreService): int
    {
        $id = (int) $this->argument('id');
        $backup = Backup::find($id);

        if (! $backup) {
            $this->error("Backup de ID {$id} não encontrado no catálogo.");

            return 1;
        }

        if (! $this->confirm("Você tem certeza de que deseja restaurar o backup {$backup->filename}? Isso irá sobrescrever o banco de dados atual!")) {
            $this->info('Restauração cancelada.');

            return 0;
        }

        $this->info('Iniciando a restauração do backup...');

        try {
            $restoreService->executeRestore($backup);
            $this->info('Backup restaurado com sucesso!');

            return 0;
        } catch (Exception $e) {
            $this->error('Erro ao realizar a restauração: '.$e->getMessage());

            return 1;
        }
    }
}
