<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Employee\CreateEmployeeAction;
use App\Actions\Employee\UpdateEmployeeAction;
use App\Actions\Employee\DeleteEmployeeAction;
use App\DTOs\Employee\CreateEmployeeDTO;
use App\DTOs\Employee\UpdateEmployeeDTO;
use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    private CreateEmployeeAction $createAction;
    private UpdateEmployeeAction $updateAction;
    private DeleteEmployeeAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateEmployeeAction::class);
        $this->updateAction = app(UpdateEmployeeAction::class);
        $this->deleteAction = app(DeleteEmployeeAction::class);
    }

    #[Test]
    public function it_can_create_an_employee_with_action_and_dto()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $dto = CreateEmployeeDTO::fromArray([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'employee_number' => 'EMP-001',
            'name' => 'João Garçom',
            'email' => 'joao@comanda.com',
            'password' => 'password123',
            'phone' => '11988887777',
            'document' => '111.222.333-44',
            'birth_date' => '1995-05-15',
            'hire_date' => '2026-06-01',
            'role' => 'waiter',
            'status' => 'active'
        ]);

        $employee = $this->createAction->execute($dto);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertDatabaseHas('employees', [
            'name' => 'João Garçom',
            'employee_number' => 'EMP-001',
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'role' => 'waiter',
        ]);
        $this->assertTrue(Hash::check('password123', $employee->password));
    }

    #[Test]
    public function it_records_an_audit_log_on_employee_creation()
    {
        $company = Company::factory()->create();

        $dto = CreateEmployeeDTO::fromArray([
            'company_id' => $company->id,
            'employee_number' => 'EMP-002',
            'name' => 'Maria Caixa',
            'email' => 'maria@comanda.com',
            'password' => 'password123',
            'role' => 'cashier',
        ]);

        $employee = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee.create',
            'context' => json_encode([
                'employee_uuid' => $employee->uuid,
                'company_id' => $company->id,
                'unit_id' => null,
                'role' => 'cashier'
            ]),
        ]);
    }

    #[Test]
    public function it_can_update_an_employee_with_action()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'name' => 'Old Name'
        ]);

        $dto = UpdateEmployeeDTO::fromArray([
            'unit_id' => null,
            'employee_number' => $employee->employee_number,
            'name' => 'Updated Name',
            'email' => $employee->email,
            'role' => 'kitchen',
            'status' => 'active'
        ]);

        $updated = $this->updateAction->execute($employee, $dto);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(EmployeeRoleEnum::KITCHEN, $updated->role);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_records_an_audit_log_on_employee_update()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $dto = UpdateEmployeeDTO::fromArray([
            'unit_id' => null,
            'employee_number' => $employee->employee_number,
            'name' => 'Novo Nome',
            'email' => $employee->email,
            'role' => $employee->role->value,
        ]);

        $this->updateAction->execute($employee, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_an_employee_with_action()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->deleteAction->execute($employee);

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_employees_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Employee::class));
    }

    #[Test]
    public function employee_with_permission_can_view_own_company_employees_policy()
    {
        $company = Company::factory()->create();
        $employee1 = Employee::factory()->create(['company_id' => $company->id]);
        $employee2 = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'employees.view']);
        $role->permissions()->attach($permission);
        $employee1->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee1)->allows('view', $employee2));
    }

    #[Test]
    public function employee_cannot_view_other_company_employees_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $employee1 = Employee::factory()->create(['company_id' => $company1->id]);
        $employee2 = Employee::factory()->create(['company_id' => $company2->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'employees.view']);
        $role->permissions()->attach($permission);
        $employee1->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee1)->allows('view', $employee2));
    }

    #[Test]
    public function employee_cannot_delete_themself_policy()
    {
        $company = Company::factory()->create();
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'employees.delete']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('delete', $employee));
    }

    #[Test]
    public function employee_can_delete_other_company_employee_policy()
    {
        $company = Company::factory()->create();
        $employee1 = Employee::factory()->create(['company_id' => $company->id]);
        $employee2 = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'employees.delete']);
        $role->permissions()->attach($permission);
        $employee1->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee1)->allows('delete', $employee2));
    }
}
