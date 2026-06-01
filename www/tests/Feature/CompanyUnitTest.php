<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Unit\CreateUnitAction;
use App\Actions\Unit\DeleteUnitAction;
use App\Actions\Unit\UpdateUnitAction;
use App\DTOs\Unit\CreateUnitDTO;
use App\DTOs\Unit\UpdateUnitDTO;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyUnitTest extends TestCase
{
    use RefreshDatabase;

    private CreateUnitAction $createAction;

    private UpdateUnitAction $updateAction;

    private DeleteUnitAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateUnitAction::class);
        $this->updateAction = app(UpdateUnitAction::class);
        $this->deleteAction = app(DeleteUnitAction::class);
    }

    #[Test]
    public function it_can_create_a_unit_with_action_and_dto()
    {
        $company = Company::factory()->create();

        $dto = CreateUnitDTO::fromArray([
            'company_id' => $company->id,
            'name' => 'Filial Centro',
            'document_number' => '12345678000199',
            'email' => 'filial.centro@comanda.com',
            'phone' => '11977776666',
            'zipcode' => '01001-000',
            'street' => 'Praça da Sé',
            'number' => '100',
            'district' => 'Sé',
            'city' => 'São Paulo',
            'state' => 'SP',
            'country' => 'Brasil',
            'status' => 'active',
        ]);

        $unit = $this->createAction->execute($dto);

        $this->assertInstanceOf(CompanyUnit::class, $unit);
        $this->assertDatabaseHas('company_units', [
            'name' => 'Filial Centro',
            'company_id' => $company->id,
            'city' => 'São Paulo',
        ]);
        $this->assertNotNull($unit->uuid);
    }

    #[Test]
    public function it_records_an_audit_log_on_unit_creation()
    {
        $company = Company::factory()->create();

        $dto = CreateUnitDTO::fromArray([
            'company_id' => $company->id,
            'name' => 'Filial Audit',
            'zipcode' => '01001-000',
            'street' => 'Rua Direita',
            'number' => '50',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        $unit = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'unit.create',
            'context' => json_encode(['unit_uuid' => $unit->uuid, 'company_id' => $company->id, 'name' => $unit->name]),
        ]);
    }

    #[Test]
    public function it_can_update_a_unit_with_action()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create([
            'company_id' => $company->id,
            'name' => 'Old Name',
        ]);

        $dto = UpdateUnitDTO::fromArray([
            'name' => 'Updated Name',
            'document_number' => $unit->document_number,
            'email' => $unit->email,
            'phone' => $unit->phone,
            'zipcode' => $unit->zipcode,
            'street' => $unit->street,
            'number' => $unit->number,
            'district' => $unit->district,
            'city' => $unit->city,
            'state' => $unit->state,
            'country' => $unit->country,
            'status' => 'active',
        ]);

        $updated = $this->updateAction->execute($unit, $dto);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertDatabaseHas('company_units', [
            'id' => $unit->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_records_an_audit_log_on_unit_update()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $dto = UpdateUnitDTO::fromArray([
            'name' => 'Novo Nome',
            'zipcode' => $unit->zipcode,
            'street' => $unit->street,
            'number' => $unit->number,
            'district' => $unit->district,
            'city' => $unit->city,
            'state' => $unit->state,
            'country' => $unit->country,
        ]);

        $this->updateAction->execute($unit, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'unit.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_unit_with_action()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $this->deleteAction->execute($unit);

        $this->assertSoftDeleted('company_units', [
            'id' => $unit->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'unit.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_units_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', CompanyUnit::class));
    }

    #[Test]
    public function employee_with_permission_can_view_own_units_policy()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['slug' => 'units.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('view', $unit));
    }

    #[Test]
    public function employee_without_permission_cannot_view_own_units_policy()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $unit));
    }

    #[Test]
    public function employee_cannot_view_other_company_units_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $unit2 = CompanyUnit::factory()->create(['company_id' => $company2->id]);
        $employee = Employee::factory()->create(['company_id' => $company1->id]);

        // Simular permissão RBAC
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['slug' => 'units.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $unit2));
    }

    #[Test]
    public function employee_cannot_delete_units_policy()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(Gate::forUser($employee)->allows('delete', $unit));
    }
}
