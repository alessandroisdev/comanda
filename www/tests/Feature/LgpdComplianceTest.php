<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\PrivacyAuditLog;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\DataAnonymizationService;
use App\Services\Privacy\DataInventoryService;
use App\Services\Privacy\DataRetentionService;
use App\Services\Privacy\DataSharingService;
use App\Services\Privacy\DataSubjectRequestService;
use App\Services\Privacy\IncidentResponseService;
use App\Services\Privacy\PrivacyComplianceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LgpdComplianceTest extends TestCase
{
    use RefreshDatabase;

    private DataAnonymizationService $anonymizer;

    private PrivacyComplianceService $complianceService;

    private DataInventoryService $inventoryService;

    private ConsentService $consentService;

    private DataSubjectRequestService $requestService;

    private DataRetentionService $retentionService;

    private IncidentResponseService $incidentService;

    private DataSharingService $sharingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->anonymizer = $this->app->make(DataAnonymizationService::class);
        $this->complianceService = $this->app->make(PrivacyComplianceService::class);
        $this->inventoryService = $this->app->make(DataInventoryService::class);
        $this->consentService = $this->app->make(ConsentService::class);
        $this->requestService = $this->app->make(DataSubjectRequestService::class);
        $this->retentionService = $this->app->make(DataRetentionService::class);
        $this->incidentService = $this->app->make(IncidentResponseService::class);
        $this->sharingService = $this->app->make(DataSharingService::class);
    }

    #[Test]
    public function it_can_mask_cpf_phone_and_email_using_data_anonymization_service(): void
    {
        $maskedCpf = $this->anonymizer->maskCpf('123.456.789-00');
        $maskedEmail = $this->anonymizer->maskEmail('alessandro@comanda.com');
        $maskedPhone = $this->anonymizer->maskPhone('(11) 99999-1234');

        $this->assertEquals('***.***.789-**', $maskedCpf);
        $this->assertEquals('a********o@comanda.com', $maskedEmail);
        $this->assertEquals('(***) *****-1234', $maskedPhone);
        $this->assertEquals('Titular Anonimizado', $this->anonymizer->anonymizeName());
    }

    #[Test]
    public function it_registers_and_validates_legal_basis_correctly(): void
    {
        $basis = $this->complianceService->registerLegalBasis(
            'Consentimento',
            'Art. 7º, I, Lei 13.709/2018',
            'Consentimento expresso do titular'
        );

        $this->assertDatabaseHas('legal_bases', [
            'uuid' => $basis->uuid,
            'name' => 'Consentimento',
        ]);

        $this->assertTrue($this->complianceService->hasLegalBasis('Consentimento'));
    }

    #[Test]
    public function it_can_inventory_personal_data_items(): void
    {
        $item = $this->inventoryService->registerItem([
            'data_name' => 'CPF do Cliente',
            'data_category' => 'confidential',
            'processing_purpose' => 'Faturamento fiscal NFC-e',
            'legal_basis' => 'Cumprimento de Obrigação Legal',
            'data_subject_type' => 'customer',
            'table_name' => 'customers',
            'column_name' => 'document',
            'retention_period' => '5 anos',
            'security_measures' => 'Criptografia',
        ]);

        $this->assertDatabaseHas('data_inventories', [
            'uuid' => $item->uuid,
            'data_name' => 'CPF do Cliente',
        ]);
    }

    #[Test]
    public function it_records_consent_grant_and_revocation_correctly(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $consent = $this->consentService->grantConsent(
            (int) $company->id,
            'customer',
            (int) $customer->id,
            $customer->uuid,
            'Newsletter e Novidades',
            'Aceito receber novidades do estabelecimento por e-mail.',
            '127.0.0.1',
            'Mozilla/5.0'
        );

        $this->assertDatabaseHas('consents', [
            'uuid' => $consent->uuid,
            'status' => 'granted',
        ]);

        $this->consentService->revokeConsent($consent->uuid);

        $this->assertDatabaseHas('consents', [
            'uuid' => $consent->uuid,
            'status' => 'revoked',
        ]);
    }

    #[Test]
    public function it_creates_erasure_requests_and_anonymizes_customers(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'name' => 'Alessandro da Silva',
            'email' => 'alessandro@cliente.com',
            'phone' => '11999999999',
            'document' => '12345678900',
        ]);

        $request = $this->requestService->createRequest(
            (int) $company->id,
            'customer',
            $customer->uuid,
            'deletion'
        );

        $this->assertDatabaseHas('data_subject_requests', [
            'uuid' => $request->uuid,
            'status' => 'pending',
        ]);

        // Executa a anonimização e soft delete
        $this->requestService->executeErasure('customer', $customer->uuid);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Titular Anonimizado',
            'phone' => null,
            'document' => null,
        ]);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    #[Test]
    public function it_applies_retention_policy_and_expurges_old_logs(): void
    {
        $company = Company::factory()->create();

        $this->retentionService->setPolicy('logs', 6, 'Obrigação Legal', 'hard_delete');

        $activeLog = PrivacyAuditLog::create([
            'company_id' => $company->id,
            'entity_type' => 'Customer',
            'entity_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'action' => 'access',
        ]);
        DB::table('privacy_audit_logs')
            ->where('id', $activeLog->id)
            ->update(['created_at' => Carbon::now()->subMonths(2)]);

        $obsoleteLog = PrivacyAuditLog::create([
            'company_id' => $company->id,
            'entity_type' => 'Customer',
            'entity_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'action' => 'access',
        ]);
        DB::table('privacy_audit_logs')
            ->where('id', $obsoleteLog->id)
            ->update(['created_at' => Carbon::now()->subMonths(8)]);

        $this->retentionService->applyRetention();

        $this->assertDatabaseHas('privacy_audit_logs', ['uuid' => $activeLog->uuid]);
        $this->assertDatabaseMissing('privacy_audit_logs', ['uuid' => $obsoleteLog->uuid]);
    }

    #[Test]
    public function it_logs_privacy_incidents_and_its_mitigation(): void
    {
        $company = Company::factory()->create();

        $incident = $this->incidentService->logIncident(
            (int) $company->id,
            'Acesso Não Autorizado',
            'high',
            'nome, e-mail',
            'Vazamento acidental devido a erro de configuração do servidor'
        );

        $this->assertDatabaseHas('privacy_incidents', [
            'uuid' => $incident->uuid,
            'status' => 'open',
        ]);

        $this->incidentService->updateMitigation(
            $incident->uuid,
            'Portas de acesso fechadas e firewall ativo',
            'resolved',
            true,
            true
        );

        $this->assertDatabaseHas('privacy_incidents', [
            'uuid' => $incident->uuid,
            'status' => 'resolved',
            'anpd_notified' => true,
        ]);
    }

    #[Test]
    public function it_logs_sharing_records_with_third_parties(): void
    {
        $company = Company::factory()->create();

        $sharing = $this->sharingService->logSharing(
            (int) $company->id,
            'Mercado Pago',
            'Faturamento de Pedido Delivery',
            'Execução de Contrato',
            ['nome', 'e-mail', 'valor'],
            'HTTPS'
        );

        $this->assertDatabaseHas('data_sharing_records', [
            'uuid' => $sharing->uuid,
            'recipient_name' => 'Mercado Pago',
        ]);
    }
}
