<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use App\Services\Backup\RestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RestoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        File::cleanDirectory(storage_path('app/backups'));
        config(['app.key' => 'base64:98Bvhu+Le0jkK/BQr7bdkGVWUDHrZ18CzZ8g14QME38=']);
        config(['app.backup_key' => '12345678901234567890123456789012']); // 32 chars
    }

    public function test_restore_fails_with_invalid_encryption_key()
    {
        $backupService = app(BackupService::class);
        $backup = $backupService->executeBackup(true);

        $restoreService = app(RestoreService::class);

        // Altera a chave temporariamente para uma inválida
        config(['app.backup_key' => 'wrongkey_wrongkey_wrongkey_wrong']); // 32 chars

        $this->expectException(\Exception::class);
        $restoreService->executeRestore($backup);
    }

    public function test_restore_fails_if_backup_file_missing_on_disk()
    {
        $backup = Backup::create([
            'filename' => 'non_existent.zip.enc',
            'path' => 'backups/non_existent.zip.enc',
            'disk' => 'local',
            'checksum' => 'dummy',
            'size_bytes' => 10,
            'is_encrypted' => true,
        ]);

        $restoreService = app(RestoreService::class);

        $this->expectException(\Exception::class);
        $restoreService->executeRestore($backup);
    }
}
