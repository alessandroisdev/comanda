<?php

namespace Tests\Feature;

use App\Services\Logging\ApplicationLogService;
use App\Services\Logging\SecurityLogService;
use App\Services\Logging\BusinessLogService;
use App\Services\Logging\AuditLogService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LoggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpar logs antigos de teste se houver
        @unlink(storage_path('logs/application.json.log'));
        @unlink(storage_path('logs/security.json.log'));
        @unlink(storage_path('logs/business.json.log'));
        @unlink(storage_path('logs/audit.json.log'));
    }

    public function test_application_log_service_writes_structured_json()
    {
        $logService = app(ApplicationLogService::class);
        $logService->info('app.info', 'Test application message', ['extra' => 'info_data']);

        $path = storage_path('logs/application.json.log');
        $this->assertTrue(File::exists($path));

        $content = File::get($path);
        $logEntry = json_decode($content, true);

        $this->assertEquals('INFO', $logEntry['level']);
        $this->assertEquals('Test application message', $logEntry['message']);
        $this->assertEquals('info_data', $logEntry['context']['extra']);
        $this->assertArrayHasKey('correlation_id', $logEntry);
        $this->assertArrayHasKey('timestamp', $logEntry);
    }

    public function test_security_log_service_redacts_sensitive_keys_for_lgpd()
    {
        $securityLog = app(SecurityLogService::class);
        $securityLog->warning('security.auth', 'User login attempt', [
            'password' => 'supersecret123',
            'card_number' => '1234-5678-9012-3456',
            'cvv' => '123',
            'safe_param' => 'visible_value'
        ]);

        $path = storage_path('logs/security.json.log');
        $this->assertTrue(File::exists($path));

        $content = File::get($path);
        $logEntry = json_decode($content, true);

        // O base log service usa a palavra '[FILTERED]' para higienizar dados em conformidade LGPD
        $this->assertEquals('[FILTERED]', $logEntry['context']['password']);
        $this->assertEquals('[FILTERED]', $logEntry['context']['card_number']);
        $this->assertEquals('[FILTERED]', $logEntry['context']['cvv']);
        $this->assertEquals('visible_value', $logEntry['context']['safe_param']);
    }

    public function test_business_log_service_writes_business_events()
    {
        $businessLog = app(BusinessLogService::class);
        $businessLog->event('business.sale', 'order_completed', [
            'order_id' => 99,
            'amount_cents' => 15000
        ]);

        $path = storage_path('logs/business.json.log');
        $this->assertTrue(File::exists($path));

        $content = File::get($path);
        $logEntry = json_decode($content, true);

        $this->assertEquals('INFO', $logEntry['level']);
        $this->assertEquals('order_completed', $logEntry['message']);
        $this->assertEquals(99, $logEntry['context']['order_id']);
        $this->assertEquals(15000, $logEntry['context']['amount_cents']);
    }

    public function test_audit_log_service_writes_access_audits()
    {
        $auditLog = app(AuditLogService::class);
        $auditLog->log('audit.access', 'user_profile_viewed', [
            'viewed_user_id' => 1
        ]);

        $path = storage_path('logs/audit.json.log');
        $this->assertTrue(File::exists($path));

        $content = File::get($path);
        $logEntry = json_decode($content, true);

        $this->assertEquals('NOTICE', $logEntry['level']);
        $this->assertEquals('user_profile_viewed', $logEntry['message']);
        $this->assertEquals(1, $logEntry['context']['viewed_user_id']);
    }
}
