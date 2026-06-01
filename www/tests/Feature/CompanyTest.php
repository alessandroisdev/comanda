<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Company\CreateCompanyAction;
use App\Actions\Company\UpdateCompanyAction;
use App\Actions\Company\DeleteCompanyAction;
use App\DTOs\Company\CreateCompanyDTO;
use App\DTOs\Company\UpdateCompanyDTO;
use App\Enums\CompanyStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Company;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private CreateCompanyAction $createAction;
    private UpdateCompanyAction $updateAction;
    private DeleteCompanyAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateCompanyAction::class);
        $this->updateAction = app(UpdateCompanyAction::class);
        $this->deleteAction = app(DeleteCompanyAction::class);
    }

    #[Test]
    public function it_can_create_a_company_with_action_and_dto()
    {
        $dto = CreateCompanyDTO::fromArray([
            'legal_name' => 'Empresa Teste LTDA',
            'trade_name' => 'Empresa Teste',
            'document_type' => 'CNPJ',
            'document_number' => '12.345.678/0001-90',
            'email' => 'financeiro@empresateste.com',
            'phone' => '11988887777',
            'status' => 'active'
        ]);

        $company = $this->createAction->execute($dto);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertDatabaseHas('companies', [
            'trade_name' => 'Empresa Teste',
            'document_number' => '12345678000190',
            'email' => 'financeiro@empresateste.com',
            'status' => 'active',
        ]);
        $this->assertNotNull($company->uuid);
    }

    #[Test]
    public function it_generates_an_audit_log_when_company_is_created()
    {
        $dto = CreateCompanyDTO::fromArray([
            'legal_name' => 'Audit Teste LTDA',
            'trade_name' => 'Audit Teste',
            'document_type' => 'CNPJ',
            'document_number' => '98.765.432/0001-10',
            'email' => 'audit@empresateste.com',
            'phone' => '11988887777',
        ]);

        $company = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.create',
            'context' => json_encode(['company_uuid' => $company->uuid, 'document_number' => '98765432000110']),
        ]);
    }

    #[Test]
    public function it_can_update_a_company_with_action_and_dto()
    {
        $company = Company::factory()->create([
            'trade_name' => 'Original Name'
        ]);

        $dto = UpdateCompanyDTO::fromArray([
            'legal_name' => $company->legal_name,
            'trade_name' => 'Updated Name',
            'document_type' => $company->document_type->value,
            'document_number' => $company->document_number,
            'email' => $company->email,
            'phone' => $company->phone,
            'status' => 'active'
        ]);

        $updated = $this->updateAction->execute($company, $dto);

        $this->assertEquals('Updated Name', $updated->trade_name);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'trade_name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_generates_an_audit_log_when_company_is_updated()
    {
        $company = Company::factory()->create();

        $dto = UpdateCompanyDTO::fromArray([
            'legal_name' => 'Novo Legal',
            'trade_name' => 'Novo Trade',
            'document_type' => $company->document_type->value,
            'document_number' => $company->document_number,
            'email' => $company->email,
            'phone' => $company->phone,
        ]);

        $this->updateAction->execute($company, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_company_with_action()
    {
        $company = Company::factory()->create();

        $this->deleteAction->execute($company);

        $this->assertSoftDeleted('companies', [
            'id' => $company->id
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'company.delete',
        ]);
    }

    #[Test]
    public function general_panel_admin_users_can_create_companies_policy()
    {
        $user = User::factory()->create(); // Usuário da tabela 'users' (Admin Geral)
        
        $this->assertTrue(Gate::forUser($user)->allows('create', Company::class));
    }

    #[Test]
    public function standard_employees_cannot_create_companies_policy()
    {
        $employee = Employee::factory()->create(); // Funcionário (não admin de panel)

        $this->assertFalse(Gate::forUser($employee)->allows('create', Company::class));
    }

    #[Test]
    public function standard_employees_can_view_own_company_policy()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);
        
        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'companies.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('view', $company));
    }

    #[Test]
    public function standard_employees_cannot_view_other_company_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company1->id]);
        
        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'companies.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $company2));
    }

    #[Test]
    public function standard_employees_cannot_delete_any_company_without_permission()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(Gate::forUser($employee)->allows('delete', $company));
    }
}
