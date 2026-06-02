<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Garante que a pasta de backups de teste esteja limpa
        File::cleanDirectory(storage_path('app/backups'));
        config(['app.key' => 'base64:98Bvhu+Le0jkK/BQr7bdkGVWUDHrZ18CzZ8g14QME38=']);
        config(['app.backup_key' => '12345678901234567890123456789012']); // 32 chars
    }

    public function test_it_can_create_backup_archive_with_encryption()
    {
        $backupService = app(BackupService::class);
        $backup = $backupService->executeBackup(true);

        $this->assertNotNull($backup);
        $this->assertTrue(File::exists(storage_path('app/'.$backup->path)));

        // O arquivo deve terminar com .zip.enc se criptografado
        $this->assertStringEndsWith('.zip.enc', $backup->path);

        // Garante que o arquivo é criptografado e não legível em texto puro como um zip normal
        $content = File::get(storage_path('app/'.$backup->path));
        // Assinatura de um arquivo zip normal começa com "PK" (50 4B 03 04)
        $this->assertStringStartsNotWith('PK', $content);

        // Verifica o status de sucesso na tabela de execuções
        $this->assertDatabaseHas('backup_executions', [
            'type' => 'backup',
            'status' => 'success',
        ]);
    }

    public function test_it_runs_backup_artisan_command()
    {
        $exitCode = Artisan::call('comanda:backup:run');
        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('backup_executions', [
            'type' => 'backup',
            'status' => 'success',
        ]);
    }

    public function test_retention_policy_cleans_old_backups()
    {
        $backupService = app(BackupService::class);

        // 1. Cria um backup simulado antigo (10 dias atrás)
        $oldPath = storage_path('app/backups/backup_old_test.zip.enc');
        @mkdir(dirname($oldPath), 0755, true);
        File::put($oldPath, 'dummy encrypted data');

        $oldBackup = Backup::create([
            'filename' => 'backup_old_test.zip.enc',
            'path' => 'backups/backup_old_test.zip.enc',
            'disk' => 'local',
            'checksum' => 'dummy-sha',
            'size_bytes' => 20,
            'is_encrypted' => true,
        ]);

        $oldBackup->created_at = Carbon::now()->subDays(10);
        $oldBackup->save();

        // 2. Executa um novo backup (isso dispara o applyRetention automaticamente no final)
        $newBackup = $backupService->executeBackup(true);

        // 3. O backup de 10 dias atrás deve ter sido deletado da base e do disco
        $this->assertDatabaseMissing('backups', ['id' => $oldBackup->id]);
        $this->assertFalse(File::exists($oldPath));

        // 4. O backup novo deve persistir
        $this->assertDatabaseHas('backups', ['id' => $newBackup->id]);
        $this->assertTrue(File::exists(storage_path('app/'.$newBackup->path)));
    }
}
